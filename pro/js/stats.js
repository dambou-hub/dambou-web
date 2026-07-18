// Statistiques pro (dambou.fr/pro/stats).
// Reproduit la logique d'agregation de stats_screen.dart (evite le double
// comptage deja corrige sur mobile : transactions liees a une commande en
// ligne exclues du bucket Caisse, commandes en ligne comptees a part).
//
// Limite connue (heritee du modele de donnees, presente aussi sur mobile) :
// une reservation payee via la Caisse cree une transaction ET reste
// comptabilisee dans le total Reservations (pas de colonne de lien entre
// transactions et bookings pour dedupliquer). A garder en tete si les totaux
// semblent legerement superieurs a la caisse physique dans ce cas precis.
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

export function getRange(periodIndex, now) {
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    if (periodIndex === 0) {
        return { start: today, end: addDays(today, 1) };
    }
    if (periodIndex === 1) {
        const day = today.getDay() === 0 ? 7 : today.getDay(); // lundi = 1
        const monday = addDays(today, -(day - 1));
        return { start: monday, end: addDays(monday, 7) };
    }
    if (periodIndex === 2) {
        return { start: new Date(now.getFullYear(), now.getMonth(), 1), end: new Date(now.getFullYear(), now.getMonth() + 1, 1) };
    }
    // periodIndex === 3 : annee
    return { start: new Date(now.getFullYear(), 0, 1), end: new Date(now.getFullYear() + 1, 0, 1) };
}
function addDays(d, n) {
    const r = new Date(d);
    r.setDate(r.getDate() + n);
    return r;
}
function toDateKey(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

export const PAY_LABELS = {
    cash: 'Especes', card: 'Carte bancaire', check: 'Cheque',
    ticket_restaurant: 'Ticket restaurant', free: 'Offert', transfer: 'Virement',
    stripe_online: 'En ligne', online: 'En ligne', dambou: 'En ligne',
};
function payLabel(m) {
    return PAY_LABELS[m] || (m ? m : 'Autres');
}

export async function loadStats(businessId, periodIndex) {
    const range = getRange(periodIndex, new Date());
    const fromIso = range.start.toISOString();
    const toIso = range.end.toISOString();
    const fromDate = toDateKey(range.start);
    const toDate = toDateKey(range.end);

    const [txRes, ordersOnlineRes, bookingsRes, exportOrdersRes, paidNotDoneRes] = await Promise.all([
        supabase.from('transactions').select('total, payment_method, source_order_id, items')
            .eq('business_id', businessId)
            .not('payment_method', 'in', '(stripe_online,online,dambou)')
            .gte('created_at', fromIso).lt('created_at', toIso),
        supabase.from('orders').select('id, total, payment_method, payment_status, is_paid')
            .eq('business_id', businessId).eq('status', 'completed')
            .in('payment_method', ['stripe_online', 'online', 'dambou'])
            .gte('created_at', fromIso).lt('created_at', toIso),
        supabase.from('bookings').select('price, payment_method, services(name, price)')
            .eq('business_id', businessId).eq('status', 'confirmed')
            .gte('booking_date', fromDate).lt('booking_date', toDate),
        supabase.from('orders').select('id, order_items(quantity, unit_price, products(name))')
            .eq('business_id', businessId)
            .or('status.eq.completed,payment_status.eq.paid')
            .not('status', 'in', '(cancelled,refunded)')
            .gte('created_at', fromIso).lt('created_at', toIso),
        supabase.from('orders').select('total')
            .eq('business_id', businessId)
            .in('payment_method', ['stripe_online', 'online'])
            .eq('payment_status', 'paid')
            .not('status', 'in', '(completed,cancelled,refunded)')
            .gte('created_at', fromIso).lt('created_at', toIso),
    ]);

    // ----- Caisse (hors paiements en ligne, deja comptes dans Commandes) -----
    const caisseByMode = {};
    let totalCaisse = 0;
    (txRes.data || []).forEach((t) => {
        const v = t.total || 0;
        const lbl = payLabel(t.payment_method);
        totalCaisse += v;
        caisseByMode[lbl] = (caisseByMode[lbl] || 0) + v;
    });

    // ----- Commandes en ligne -----
    let totalCommandes = 0;
    let nbCommandes = 0;
    (ordersOnlineRes.data || []).forEach((o) => {
        totalCommandes += o.total || 0;
        nbCommandes++;
    });
    const paidNotDone = paidNotDoneRes.data || [];
    const totalPaidNotDone = paidNotDone.reduce((s, o) => s + (o.total || 0), 0);
    totalCommandes += totalPaidNotDone;
    nbCommandes += paidNotDone.length;

    // ----- Reservations -----
    let totalRdv = 0, nbRdv = 0, rdvEnLigne = 0;
    const rdvSurPlaceByMode = {};
    (bookingsRes.data || []).forEach((b) => {
        const v = b.price || (b.services && b.services.price) || 0;
        const method = b.payment_method || '';
        nbRdv++;
        totalRdv += v;
        if (method === 'stripe_online' || method === 'online') {
            rdvEnLigne += v;
        } else {
            const lbl = payLabel(method);
            rdvSurPlaceByMode[lbl] = (rdvSurPlaceByMode[lbl] || 0) + v;
        }
    });

    // ----- Top articles (order_items + transactions, sans doublon commande/caisse) -----
    const productStats = {};
    function addStat(name, qty, unitPrice) {
        if (!name) return;
        if (!productStats[name]) productStats[name] = { name: name, qty: 0, revenue: 0 };
        productStats[name].qty += qty;
        productStats[name].revenue += unitPrice * qty;
    }
    const exportOrders = exportOrdersRes.data || [];
    const orderItemNames = {};
    exportOrders.forEach((order) => {
        const names = new Set();
        (order.order_items || []).forEach((item) => {
            const name = (item.products && item.products.name) || '';
            if (name) addStat(name, item.quantity || 1, item.unit_price || 0);
            if (name) names.add(name);
        });
        orderItemNames[order.id] = names;
    });
    (txRes.data || []).forEach((txn) => {
        const already = txn.source_order_id ? (orderItemNames[txn.source_order_id] || new Set()) : new Set();
        (txn.items || []).forEach((item) => {
            if (already.has(item.name)) return;
            addStat(item.name, item.qty || 1, item.unit_price || 0);
        });
    });
    const topProducts = Object.values(productStats).sort((a, b) => b.qty - a.qty);

    return {
        totalCaisse, caisseByMode,
        totalCommandes, nbCommandes, totalPaidNotDone, nbPaidNotDone: paidNotDone.length,
        totalRdv, nbRdv, rdvEnLigne, rdvSurPlaceByMode,
        totalGlobal: totalCaisse + totalCommandes + totalRdv,
        topProducts,
    };
}
