// Commandes pro (dambou.fr/pro/commandes).
// Reproduit orders_screen.dart + new_order_screen.dart.
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

const ORDER_SELECT = 'id, status, is_paid, payment_status, payment_method, payment_intent_id, stripe_account_id, total, order_number, pickup_time, notes, created_at, customer_id, order_items(id, quantity, unit_price, product_id, customization_note, selected_ingredients, products(name, needs_prep)), users(first_name, last_name, phone)';

// Commandes actives (non terminees), reproduit la requete principale de
// orders_screen.dart.
export async function loadOrders(businessId) {
    const { data, error } = await supabase.from('orders').select(ORDER_SELECT)
        .eq('business_id', businessId)
        .neq('status', 'completed')
        .order('created_at', { ascending: false });
    if (error) { console.error('Erreur chargement commandes:', error); return []; }
    return data || [];
}

// Fix par rapport a orders_screen.dart : la requete principale exclut
// 'completed' (.neq('status','completed')), donc l'onglet Terminees cote
// mobile ne peut en pratique afficher que les no-show, jamais une vraie
// commande terminee. On charge ici explicitement les dernieres commandes
// terminees (limitees, pour eviter une requete non bornee).
export async function loadCompletedOrders(businessId, limit) {
    const { data, error } = await supabase.from('orders').select(ORDER_SELECT)
        .eq('business_id', businessId)
        .eq('status', 'completed')
        .order('created_at', { ascending: false })
        .limit(limit || 50);
    if (error) { console.error('Erreur chargement commandes terminees:', error); return []; }
    return data || [];
}

function pickupMinutes(order) {
    const pt = order.pickup_time;
    if (!pt) return 9999;
    const parts = pt.split(':');
    return (parseInt(parts[0], 10) || 0) * 60 + (parseInt(parts[1], 10) || 0);
}

// Repartit les commandes actives dans les 7 categories de orders_screen.dart.
export function categorizeOrders(activeOrders, completedOrders) {
    const now = new Date();
    const nowMin = now.getHours() * 60 + now.getMinutes();

    const toConfirm = activeOrders.filter((o) => o.status === 'pending')
        .sort((a, b) => pickupMinutes(a) - pickupMinutes(b));
    const confirmedList = activeOrders.filter((o) => o.status === 'confirmed');
    const soon = confirmedList.filter((o) => {
        const pt = pickupMinutes(o);
        return pt === 9999 || (pt - nowMin) <= 30;
    }).sort((a, b) => pickupMinutes(a) - pickupMinutes(b));
    const later = confirmedList.filter((o) => {
        const pt = pickupMinutes(o);
        return pt !== 9999 && (pt - nowMin) > 30;
    }).sort((a, b) => pickupMinutes(a) - pickupMinutes(b));
    const inProgress = activeOrders.filter((o) => o.status === 'preparing');
    const ready = activeOrders.filter((o) => o.status === 'ready');
    const cancelled = activeOrders.filter((o) => o.status === 'cancelled' || o.status === 'no_show');
    const done = completedOrders || [];

    return { toConfirm, soon, later, inProgress, ready, done, cancelled };
}

const STATUS_NOTIF = {
    confirmed: { title: 'Commande confirmee', message: (n) => 'Votre commande ' + n + ' a ete confirmee.' },
    preparing: { title: 'Preparation en cours', message: (n) => 'Votre commande ' + n + ' est en cours de preparation.' },
    ready: { title: 'Commande prete !', message: (n) => 'Votre commande ' + n + ' est prete a etre recuperee.' },
    cancelled: { title: 'Commande annulee', message: (n) => 'Votre commande ' + n + ' a ete annulee.' },
    completed: { title: 'Merci !', message: (n) => 'Votre commande ' + n + ' a bien ete recuperee. A bientot !' },
};

export async function updateOrderStatus(orderId, newStatus) {
    const { error } = await supabase.from('orders')
        .update({ status: newStatus, updated_at: new Date().toISOString() }).eq('id', orderId);
    if (error) throw error;

    try {
        const { data: order } = await supabase.from('orders').select('customer_id, order_number, total, business_id')
            .eq('id', orderId).maybeSingle();
        if (order && order.customer_id) {
            const notif = STATUS_NOTIF[newStatus];
            if (notif) {
                await supabase.from('notifications').insert({
                    user_id: order.customer_id, title: notif.title, message: notif.message(order.order_number || ''),
                    type: 'order', is_read: false, sent_at: new Date().toISOString(),
                    data: { order_id: orderId, order_number: order.order_number },
                });
            }
        }
        if (newStatus === 'completed' && order && order.customer_id) {
            await creditLoyaltyForOrder(order.business_id, order.customer_id, order.total || 0);
        }
    } catch (e) { /* ignore, non bloquant */ }
}

async function creditLoyaltyForOrder(businessId, customerId, amount) {
    try {
        const { data: biz } = await supabase.from('businesses').select('loyalty_enabled, loyalty_points_per_euro')
            .eq('id', businessId).maybeSingle();
        if (!biz || biz.loyalty_enabled !== true) return;
        const pts = Math.round(amount * (biz.loyalty_points_per_euro || 1));
        if (pts <= 0) return;
        const { data: card } = await supabase.from('loyalty_cards').select('*')
            .eq('business_id', businessId).eq('customer_id', customerId).maybeSingle();
        if (!card) {
            const { data: newCard } = await supabase.from('loyalty_cards').upsert({
                business_id: businessId, customer_id: customerId, points: pts, stamps: 1, total_visits: 1, total_spent: amount,
            }, { onConflict: 'business_id,customer_id' }).select().single();
            await supabase.from('loyalty_transactions').insert({
                loyalty_card_id: newCard.id, business_id: businessId, customer_id: customerId,
                type: 'earn', points: pts, stamps: 1, amount_spent: amount, description: 'Commande terminee',
            });
        } else {
            await supabase.from('loyalty_cards').update({
                points: (card.points || 0) + pts, stamps: (card.stamps || 0) + 1,
                total_visits: (card.total_visits || 0) + 1, total_spent: (card.total_spent || 0) + amount,
                updated_at: new Date().toISOString(),
            }).eq('id', card.id);
            await supabase.from('loyalty_transactions').insert({
                loyalty_card_id: card.id, business_id: businessId, customer_id: customerId,
                type: 'earn', points: pts, stamps: 1, amount_spent: amount, description: 'Commande terminee',
            });
        }
    } catch (e) { /* ignore */ }
}

export async function editPickupTime(orderId, newTime) {
    const { error } = await supabase.from('orders')
        .update({ pickup_time: newTime, updated_at: new Date().toISOString() }).eq('id', orderId);
    if (error) throw error;
    try {
        const { data: order } = await supabase.from('orders').select('customer_id, order_number')
            .eq('id', orderId).maybeSingle();
        if (order && order.customer_id) {
            await supabase.from('notifications').insert({
                user_id: order.customer_id, title: 'Heure de retrait modifiee',
                message: 'Votre commande ' + (order.order_number || '') + ' sera prete a ' + newTime + '.',
                type: 'order', is_read: false, sent_at: new Date().toISOString(),
                data: { order_id: orderId },
            });
        }
    } catch (e) { /* ignore */ }
}

// Rembourse via Stripe (meme Edge Function stripe-payment que l'app mobile,
// action 'refund_payment') puis annule la commande. N'echoue pas si le
// remboursement echoue (comme cote mobile, catch silencieux) -- l'annulation
// se fait quand meme, le pro devra gerer un remboursement manuel si besoin.
export async function cancelOrderWithRefund(order) {
    const isPaidOnline = order.payment_status === 'paid' && order.payment_method === 'stripe_online';
    if (isPaidOnline && order.payment_intent_id) {
        try {
            await supabase.functions.invoke('stripe-payment', {
                body: {
                    action: 'refund_payment',
                    payment_intent_id: order.payment_intent_id,
                    stripe_account_id: order.stripe_account_id || '',
                },
            });
        } catch (e) { console.error('Erreur remboursement Stripe:', e); }
    }
    await updateOrderStatus(order.id, 'cancelled');
}

export function isPaidOnline(order) {
    return order.payment_status === 'paid' && order.payment_method === 'stripe_online';
}
export function orderNeedsPrep(order) {
    return (order.order_items || []).some((item) => item.products && item.products.needs_prep);
}

// ------------------------------------------------------------
// NOUVELLE COMMANDE (creation manuelle par le pro)
// ------------------------------------------------------------
export async function loadProductsForNewOrder(businessId) {
    const { data: products } = await supabase.from('products')
        .select('id, name, price, category_id, categories(name)')
        .eq('business_id', businessId).eq('is_active', true).order('sort_order');
    const productList = products || [];
    const ids = productList.map((p) => p.id);
    let ingByProduct = {};
    if (ids.length) {
        const { data: ings } = await supabase.from('ingredients').select('name, product_id').in('product_id', ids);
        (ings || []).forEach((ing) => {
            if (!ingByProduct[ing.product_id]) ingByProduct[ing.product_id] = [];
            ingByProduct[ing.product_id].push(ing.name);
        });
    }
    const allIngsSet = new Set();
    Object.values(ingByProduct).forEach((list) => list.forEach((n) => allIngsSet.add(n)));
    return { products: productList, ingByProduct: ingByProduct, allIngredients: Array.from(allIngsSet).sort() };
}

export async function createManualOrder(params) {
    const num = 'CMD-' + Date.now().toString().slice(-7);
    const notesParts = [];
    if (params.customerName) notesParts.push('Client : ' + params.customerName);
    if (params.customerPhone) notesParts.push('Tel : ' + params.customerPhone);

    const insertData = {
        business_id: params.businessId,
        order_number: num,
        status: 'confirmed',
        total: params.total,
        notes: notesParts.join(' | '),
        pickup_time: params.pickupTime || null,
        created_at: new Date().toISOString(),
    };
    if (params.customerId) insertData.customer_id = params.customerId;

    const { data: orderRes, error } = await supabase.from('orders').insert(insertData).select('id').single();
    if (error) throw error;
    const orderId = orderRes.id;

    const orderItems = params.lines.map((line) => ({
        order_id: orderId,
        product_id: line.productId,
        quantity: 1,
        unit_price: line.unitPrice,
        total_price: line.unitPrice,
        customization_note: line.note || null,
        selected_ingredients: line.selectedIngredients.join(', '),
    }));
    const { error: itemsError } = await supabase.from('order_items').insert(orderItems);
    if (itemsError) throw itemsError;
    return orderId;
}
