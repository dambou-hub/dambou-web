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

const MONTH_LABELS = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

// ------------------------------------------------------------
// EXPORT CSV COMPTABLE (reproduit csv_export_service.dart, version
// avec colonnes Nature/Famille et TVA ponderee par article)
// ------------------------------------------------------------
const EXPORT_PAY_LABELS = {
    cash: 'Espèces', card: 'Carte bancaire', check: 'Chèque', free: 'Offert',
    dambou: 'Dambou (en ligne)', online: 'Dambou (en ligne)',
    card_terminal: 'CB Terminal', transfer: 'Virement',
};
function exportPayLabel(m) {
    return EXPORT_PAY_LABELS[m] || (m ? m : 'Dambou (en ligne)');
}
function exportCustomerName(person) {
    if (!person) return 'Client';
    return ((person.first_name || '') + ' ' + (person.last_name || '')).trim() || 'Client';
}
function fmtNum(v) {
    return (v || 0).toFixed(2).replace('.', ',');
}

export async function loadExportData(businessId, periodIndex) {
    const range = getRange(periodIndex, new Date());
    const fromIso = range.start.toISOString();
    const toIso = range.end.toISOString();
    const fromDate = toDateKey(range.start);
    const toDate = toDateKey(range.end);

    const [ordersRes, bookingsRes, txRes] = await Promise.all([
        supabase.from('orders').select('*, order_items(quantity, unit_price, products(name, tva_rate, category_id, categories(name))), users(first_name, last_name)')
            .eq('business_id', businessId)
            .or('status.eq.completed,payment_status.eq.paid')
            .not('status', 'in', '(cancelled,refunded)')
            .gte('created_at', fromIso).lt('created_at', toIso),
        supabase.from('bookings').select('*, services(name, price, tva_rate, category_id, categories(name))')
            .eq('business_id', businessId).eq('status', 'confirmed')
            .gte('booking_date', fromDate).lt('booking_date', toDate),
        supabase.from('transactions').select('*')
            .eq('business_id', businessId)
            .gte('created_at', fromIso).lt('created_at', toIso),
    ]);

    return {
        orders: ordersRes.data || [],
        bookings: bookingsRes.data || [],
        transactions: txRes.data || [],
        errors: [ordersRes.error, bookingsRes.error, txRes.error].filter(Boolean),
    };
}

export function buildCsv({ businessName, orders, bookings, posTransactions, periodLabel, currency, isTvaAssujetti }) {
    const rows = [];
    const now = new Date();
    const pad2 = (n) => String(n).padStart(2, '0');
    const fmtDate = (d) => pad2(d.getDate()) + '/' + pad2(d.getMonth() + 1) + '/' + d.getFullYear();
    const fmtTime = (d) => pad2(d.getHours()) + ':' + pad2(d.getMinutes());

    const headers = ['N° Facture', 'Date', 'Heure', 'Type opération', 'Produit/Service', 'Famille', 'Description', 'Client', 'Mode paiement', 'Montant TTC (' + currency + ')'];
    if (isTvaAssujetti) headers.push('Taux TVA (%)', 'Montant TVA (' + currency + ')', 'Montant HT (' + currency + ')');
    rows.push(headers);

    let invoiceCounter = 1;
    function nextInvoiceId() {
        const id = 'F' + now.getFullYear() + '-' + String(invoiceCounter).padStart(4, '0');
        invoiceCounter++;
        return id;
    }

    const allLines = [];

    // ----- Commandes : famille(s) + TVA ponderee par ligne d'article -----
    orders.forEach((o) => {
        const date = o.created_at ? new Date(o.created_at) : null;
        const total = o.total || 0;
        const items = o.order_items || [];
        const desc = items.map((i) => (i.quantity || 1) + 'x ' + ((i.products && i.products.name) || '')).join(', ');
        const familles = [...new Set(items.map((i) => i.products && i.products.categories && i.products.categories.name).filter(Boolean))];
        let sumTvaWeighted = 0, sumBase = 0;
        items.forEach((i) => {
            const qty = i.quantity || 1;
            const lineTotal = qty * (i.unit_price || 0);
            const rate = i.products && i.products.tva_rate;
            if (rate != null) { sumTvaWeighted += rate * lineTotal; sumBase += lineTotal; }
        });
        const effTva = sumBase > 0 ? sumTvaWeighted / sumBase : null;
        allLines.push({ date, type: 'order', total, desc, famille: familles.join(', '), effectiveTva: effTva, data: o });
    });

    // ----- Reservations : famille + TVA du service -----
    bookings.forEach((b) => {
        const dateTimeStr = b.booking_date + (b.start_time ? 'T' + b.start_time : '');
        const date = new Date(dateTimeStr);
        const svc = b.services;
        const price = b.price || (svc && svc.price) || 0;
        if (isNaN(date.getTime()) || price <= 0) return;
        const famille = (svc && svc.categories && svc.categories.name) || '';
        const effTva = svc ? svc.tva_rate : null;
        allLines.push({ date, type: 'booking', total: price, desc: '', famille, effectiveTva: effTva, data: b });
    });

    // ----- Caisse directe : famille(s) + TVA ponderee (items enrichis par la caisse) -----
    posTransactions.forEach((t) => {
        const date = t.created_at ? new Date(t.created_at) : null;
        const items = t.items || [];
        const familles = [...new Set(items.map((i) => i.category).filter(Boolean))];
        let sumTvaWeighted = 0, sumBase = 0;
        items.forEach((i) => {
            const lineTotal = i.total || 0;
            const rate = i.tva_rate;
            if (rate != null) { sumTvaWeighted += rate * lineTotal; sumBase += lineTotal; }
        });
        const effTva = sumBase > 0 ? (sumTvaWeighted / sumBase) : (t.tva_rate != null ? t.tva_rate : null);
        allLines.push({ date, type: 'pos', total: t.total || 0, desc: '', famille: familles.join(', '), effectiveTva: effTva, data: t });
    });

    allLines.sort((a, b) => {
        if (!a.date && !b.date) return 0;
        if (!a.date) return -1;
        if (!b.date) return 1;
        return a.date - b.date;
    });

    let totalTtc = 0, totalTva = 0, totalHt = 0;
    const typeCount = {};

    allLines.forEach((line) => {
        const { date, type, total, famille, data: o } = line;
        totalTtc += total;
        let typeLabel, desc, client, payMethod, natureLabel;

        if (type === 'order') {
            typeLabel = 'Vente en ligne';
            desc = line.desc;
            client = exportCustomerName(o.users);
            payMethod = exportPayLabel(o.payment_method || 'dambou');
            natureLabel = 'Produit'; // le module commande ne vend que des produits
        } else if (type === 'booking') {
            typeLabel = 'Prestation de service';
            desc = (o.services && o.services.name) || 'Prestation';
            client = o.customer_id ? exportCustomerName(null) : exportCustomerName(o.manual_customer_name ? { first_name: o.manual_customer_name, last_name: '' } : null);
            payMethod = exportPayLabel(o.payment_method || 'dambou');
            natureLabel = 'Service';
        } else {
            typeLabel = 'Vente caisse directe';
            const items = o.items || [];
            desc = items.map((i) => (i.qty || 1) + 'x ' + (i.name || '')).join(', ') || 'Vente directe';
            client = o.customer_name || 'Client direct';
            payMethod = exportPayLabel(o.payment_method || '');
            const hasProduct = items.some((i) => i.is_service !== true);
            const hasService = items.some((i) => i.is_service === true);
            natureLabel = (hasProduct && hasService) ? 'Mixte' : hasService ? 'Service' : 'Produit';
        }

        typeCount[typeLabel] = (typeCount[typeLabel] || 0) + total;

        // Taux effectif : celui calcule depuis les articles (pondere si mixte),
        // sinon 20% par defaut si aucune donnee de taux n'est disponible.
        const tvaRate = line.effectiveTva != null ? line.effectiveTva : 20.0;
        const tvaAmt = isTvaAssujetti ? total - (total / (1 + tvaRate / 100)) : 0;
        const ht = isTvaAssujetti ? total - tvaAmt : total;
        totalTva += tvaAmt;
        totalHt += ht;

        const row = [nextInvoiceId(), date ? fmtDate(date) : '', date ? fmtTime(date) : '', typeLabel, natureLabel, famille, desc, client, payMethod, fmtNum(total)];
        if (isTvaAssujetti) row.push(fmtNum(tvaRate), fmtNum(tvaAmt), fmtNum(ht));
        rows.push(row);
    });

    rows.push([]);
    rows.push(['=== RÉCAPITULATIF PAR TYPE ===']);
    rows.push(['Type', 'Montant TTC (' + currency + ')']);
    Object.keys(typeCount).forEach((k) => rows.push([k, fmtNum(typeCount[k])]));

    rows.push([]);
    if (isTvaAssujetti) {
        rows.push(['=== TOTAUX PÉRIODE : ' + periodLabel + ' ===']);
        rows.push(['', '', '', '', '', '', 'TOTAL HT', '', '', fmtNum(totalHt)]);
        rows.push(['', '', '', '', '', '', 'TOTAL TVA', '', '', fmtNum(totalTva)]);
        rows.push(['', '', '', '', '', '', 'TOTAL TTC', '', '', fmtNum(totalTtc)]);
    } else {
        rows.push(['=== TOTAL PÉRIODE : ' + periodLabel + ' ===']);
        rows.push(['', '', '', '', '', '', 'TOTAL', '', '', fmtNum(totalTtc)]);
    }

    rows.push([]);
    rows.push(['Export généré par Dambou le ' + fmtDate(now) + ' à ' + fmtTime(now)]);
    rows.push(['Professionnel : ' + businessName]);
    rows.push(['Période : ' + periodLabel]);
    rows.push(['Nombre de transactions : ' + allLines.length]);

    const csv = rows.map((row) => row.map((cell) => '"' + String(cell).replace(/"/g, '""') + '"').join(';')).join('\n');
    return '\uFEFF' + csv;
}

export function downloadCsv(csvContent, periodLabel) {
    const now = new Date();
    const safePeriod = periodLabel.replace(/[^a-zA-Z0-9_-]/g, '_').replace(/_+/g, '_');
    const filename = 'dambou_' + safePeriod + '_' + now.getFullYear() + String(now.getMonth() + 1).padStart(2, '0') + String(now.getDate()).padStart(2, '0') + '.csv';

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

export const PAY_LABELS = {
    cash: 'Espèces', card: 'Carte bancaire', check: 'Chèque',
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
        supabase.from('transactions').select('total, payment_method, source_order_id, items, created_at')
            .eq('business_id', businessId)
            .not('payment_method', 'in', '(stripe_online,online,dambou)')
            .gte('created_at', fromIso).lt('created_at', toIso),
        supabase.from('orders').select('id, total, payment_method, payment_status, is_paid, created_at')
            .eq('business_id', businessId).eq('status', 'completed')
            .in('payment_method', ['stripe_online', 'online', 'dambou'])
            .gte('created_at', fromIso).lt('created_at', toIso),
        supabase.from('bookings').select('price, payment_method, booking_date, services(name, price)')
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

    // On journalise chaque erreur individuellement au lieu de les avaler
    // silencieusement (un tableau vide en cas d'erreur ressemble a "aucune donnee").
    const errors = [];
    [
        ['transactions (caisse)', txRes],
        ['orders (en ligne)', ordersOnlineRes],
        ['bookings', bookingsRes],
        ['orders (export/top articles)', exportOrdersRes],
        ['orders (payees non terminees)', paidNotDoneRes],
    ].forEach(([label, res]) => {
        if (res.error) {
            console.error('Erreur stats -', label, ':', res.error);
            errors.push(label + ' : ' + res.error.message);
        }
    });

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

    // ----- Tendance temporelle (reutilise les donnees deja chargees, pas de requete en plus) -----
    // Semaine/Mois : bucket par jour. Annee : bucket par mois (365 points serait illisible).
    const byMonth = periodIndex === 3;
    const trendMap = {};
    function bucketKey(dateStr) {
        return byMonth ? dateStr.substring(0, 7) : dateStr.substring(0, 10);
    }
    function addToTrend(dateStr, amount) {
        const key = bucketKey(dateStr);
        trendMap[key] = (trendMap[key] || 0) + amount;
    }
    (txRes.data || []).forEach((t) => addToTrend(t.created_at, t.total || 0));
    (ordersOnlineRes.data || []).forEach((o) => addToTrend(o.created_at, o.total || 0));
    (bookingsRes.data || []).forEach((b) => addToTrend(b.booking_date, b.price || (b.services && b.services.price) || 0));

    // Genere tous les buckets de la periode, meme a 0, pour un graphique continu.
    const trend = [];
    if (periodIndex !== 0) {
        if (byMonth) {
            for (let m = 0; m < 12; m++) {
                const key = range.start.getFullYear() + '-' + String(m + 1).padStart(2, '0');
                trend.push({ key: key, label: MONTH_LABELS[m], total: trendMap[key] || 0 });
            }
        } else {
            let cursor = new Date(range.start);
            while (cursor < range.end) {
                const key = toDateKey(cursor);
                trend.push({ key: key, label: String(cursor.getDate()), total: trendMap[key] || 0 });
                cursor = addDays(cursor, 1);
            }
        }
    }

    return {
        totalCaisse, caisseByMode,
        totalCommandes, nbCommandes, totalPaidNotDone, nbPaidNotDone: paidNotDone.length,
        totalRdv, nbRdv, rdvEnLigne, rdvSurPlaceByMode,
        totalGlobal: totalCaisse + totalCommandes + totalRdv,
        topProducts,
        trend,
        errors,
    };
}
