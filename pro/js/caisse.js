// Caisse pro (dambou.fr/pro/caisse).
// Reproduit pos_screen.dart pour la vente libre (hors forfaits/abonnements, hors commande source).
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

export const PAYMENT_METHOD_LABELS = {
    cash: 'Espèces',
    card: 'Carte',
    check: 'Chèque',
    ticket_restaurant: 'Ticket resto',
    free: 'Offert',
};

// Produits + services actifs du business, fusionnes pour la grille de vente.
export async function loadCatalogueItems(businessId) {
    const [prodRes, svcRes] = await Promise.all([
        supabase.from('products').select('*, categories(name)').eq('business_id', businessId).eq('is_active', true).order('sort_order'),
        supabase.from('services').select('*, categories(name)').eq('business_id', businessId).eq('is_active', true).order('sort_order'),
    ]);
    const products = (prodRes.data || []).map((p) => Object.assign({ _isProduct: true }, p));
    const services = (svcRes.data || []).map((s) => Object.assign({ _isProduct: false }, s));
    return products.concat(services);
}

// Fiche fidelite d'un client pour ce business (ou null si aucune / pas encore de compte).
export async function loadLoyaltyCard(businessId, customerId) {
    if (!customerId) return null;
    const { data } = await supabase.from('loyalty_cards').select('*').eq('business_id', businessId).eq('customer_id', customerId).maybeSingle();
    return data || null;
}

// Encaisse le panier (reproduit _pay() de pos_screen.dart, sans forfaits/source_order_id).
// cart: array de {id, name, unitPrice, qty, isProduct}
// client: null | {type:'dambou', id, name, phone} | {type:'guest', name, phone}
export async function payCart(params) {
    const { cart, business, client, discountAmount, discountType, loyaltyDiscount, useLoyalty, loyaltyCard, method, bookingId } = params;

    const subtotal = cart.reduce((sum, i) => sum + i.unitPrice * i.qty, 0);
    const total = Math.max(subtotal - (discountAmount || 0) - (loyaltyDiscount || 0), 0);

    const ticketNum = 'TKT-' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '-' + Date.now().toString().slice(-6);
    const items = cart.map((i) => {
        const catName = (params.categories || []).find((c) => c.id === i.categoryId);
        return {
            name: i.name, qty: i.qty, unit_price: i.unitPrice, total: i.unitPrice * i.qty,
            tva_rate: i.tvaRate != null ? i.tvaRate : null,
            category: catName ? catName.name : null,
            is_service: !i.isProduct,
        };
    });
    // TVA moyenne ponderee par montant (fallback si le panier melange plusieurs taux)
    let weightedTvaSum = 0, weightedTvaBase = 0;
    cart.forEach((i) => {
        if (i.tvaRate != null) {
            weightedTvaSum += i.tvaRate * (i.unitPrice * i.qty);
            weightedTvaBase += (i.unitPrice * i.qty);
        }
    });
    const avgTvaRate = weightedTvaBase > 0 ? weightedTvaSum / weightedTvaBase : null;

    const insertData = {
        business_id: business.id,
        items: items,
        subtotal: subtotal,
        discount: discountAmount || 0,
        discount_type: discountType || 'amount',
        loyalty_discount: loyaltyDiscount || 0,
        total: total,
        payment_method: method,
        ticket_number: ticketNum,
    };
    if (avgTvaRate != null) insertData.tva_rate = avgTvaRate;
    const customerId = client && client.type === 'dambou' ? client.id : null;
    const customerName = client ? client.name : '';
    if (customerId) insertData.customer_id = customerId;
    if (customerName) insertData.customer_name = customerName;

    const { data: txn, error: txnError } = await supabase.from('transactions').insert(insertData).select('id').single();
    if (txnError) throw txnError;

    if (bookingId) {
        try {
            await supabase.from('bookings').update({ is_paid: true, payment_method: method, status: 'confirmed' }).eq('id', bookingId);
        } catch (e) { /* ignore */ }
    }

    // Decompte du stock pour les produits suivis
    for (const item of cart) {
        if (!item.isProduct) continue;
        try {
            const { data: prod } = await supabase.from('products').select('stock_qty, track_stock').eq('id', item.id).maybeSingle();
            if (prod && prod.track_stock) {
                const newQty = Math.max((prod.stock_qty || 0) - item.qty, 0);
                await supabase.from('products').update({ stock_qty: newQty }).eq('id', item.id);
            }
        } catch (e) { /* ignore */ }
    }

    // Fidelite : uniquement pour un vrai client Dambou
    if (customerId && business.loyalty_enabled) {
        const ptsPerEuro = business.loyalty_points_per_euro || 1;
        const rewardVal = business.loyalty_reward_value || 5;
        const pointsEarned = Math.round(total * ptsPerEuro);

        if (!loyaltyCard) {
            const { data: newCard } = await supabase.from('loyalty_cards').upsert({
                business_id: business.id, customer_id: customerId,
                points: pointsEarned, stamps: 1, total_visits: 1, total_spent: total,
            }, { onConflict: 'business_id,customer_id' }).select().single();
            await supabase.from('loyalty_transactions').insert({
                loyalty_card_id: newCard.id, business_id: business.id, customer_id: customerId,
                type: 'earn', points: pointsEarned, stamps: 1, amount_spent: total, description: 'Achat en caisse',
            });
        } else {
            const currentPts = loyaltyCard.points || 0;
            const ptsToRedeem = useLoyalty ? Math.round((loyaltyDiscount || 0) / rewardVal * 100) : 0;
            await supabase.from('loyalty_cards').update({
                points: Math.max(currentPts - ptsToRedeem + (useLoyalty ? 0 : pointsEarned), 0),
                stamps: (loyaltyCard.stamps || 0) + 1,
                total_visits: (loyaltyCard.total_visits || 0) + 1,
                total_spent: (loyaltyCard.total_spent || 0) + total,
                updated_at: new Date().toISOString(),
            }).eq('id', loyaltyCard.id);
            await supabase.from('loyalty_transactions').insert({
                loyalty_card_id: loyaltyCard.id, business_id: business.id, customer_id: customerId,
                type: useLoyalty ? 'redeem' : 'earn',
                points: useLoyalty ? -ptsToRedeem : pointsEarned,
                stamps: 1, amount_spent: total,
                description: useLoyalty ? 'Remise fidelite' : 'Achat en caisse',
            });
        }
    }

    return txn.id;
}
