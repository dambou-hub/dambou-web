// Clients pro (dambou.fr/pro/clients + /pro/client + /pro/manual-client).
// Reproduit client_detail_screen.dart (client Dambou) et manual_client_screen.dart (client manuel).
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

// ------------------------------------------------------------
// CLIENT DAMBOU (compte app) -- 3 onglets : en cours / historique / fidelite
// ------------------------------------------------------------
export async function loadDambouClientDetail(businessId, customerId) {
    const todayKey = new Date().toISOString().substring(0, 10);

    const [clientRes, activeBookingsRes, activeOrdersRes, historyBookingsRes, historyOrdersRes, historyTxRes, loyaltyCardRes, loyaltyHistoryRes] = await Promise.all([
        supabase.from('users').select('id, first_name, last_name, phone, email').eq('id', customerId).single(),
        supabase.from('bookings').select('id, booking_date, start_time, status, services(name, duration)')
            .eq('business_id', businessId).eq('customer_id', customerId)
            .in('status', ['pending', 'confirmed']).gte('booking_date', todayKey).order('booking_date'),
        supabase.from('orders').select('id, total, status, created_at, order_items(quantity, products(name))')
            .eq('business_id', businessId).eq('customer_id', customerId)
            .in('status', ['pending', 'confirmed', 'preparing', 'ready']).order('created_at', { ascending: false }),
        supabase.from('bookings').select('id, booking_date, start_time, status, is_paid, payment_method, services(name, price)')
            .eq('business_id', businessId).eq('customer_id', customerId)
            .in('status', ['completed', 'confirmed', 'cancelled', 'no_show']).lte('booking_date', todayKey)
            .order('booking_date', { ascending: false }).limit(30),
        supabase.from('orders').select('id, created_at, status, total, order_items(quantity, products(name))')
            .eq('business_id', businessId).eq('customer_id', customerId)
            .in('status', ['completed', 'paid', 'cancelled']).order('created_at', { ascending: false }).limit(20),
        supabase.from('transactions').select('id, created_at, total, payment_method, items')
            .eq('business_id', businessId).eq('customer_id', customerId)
            .order('created_at', { ascending: false }).limit(20),
        supabase.from('loyalty_cards').select('*').eq('business_id', businessId).eq('customer_id', customerId).maybeSingle(),
        supabase.from('loyalty_transactions').select('*').eq('customer_id', customerId).order('created_at', { ascending: false }).limit(20),
    ]);

    const historyBookings = (historyBookingsRes.data || []).map((b) => Object.assign({ _type: 'booking' }, b));
    const historyOrders = (historyOrdersRes.data || []).map((o) => Object.assign({ _type: 'order' }, o));
    const historyTx = (historyTxRes.data || []).map((t) => Object.assign({ _type: 'transaction' }, t));
    const history = historyBookings.concat(historyOrders, historyTx).sort((a, b) => {
        const da = a.booking_date || a.created_at || '';
        const db = b.booking_date || b.created_at || '';
        return db.localeCompare(da);
    });

    return {
        client: clientRes.data || null,
        activeBookings: activeBookingsRes.data || [],
        activeOrders: activeOrdersRes.data || [],
        history: history,
        loyaltyCard: loyaltyCardRes.data || null,
        loyaltyHistory: loyaltyHistoryRes.data || [],
    };
}

// ------------------------------------------------------------
// CLIENT MANUEL (fiche cote pro, pas de compte app)
// ------------------------------------------------------------
export async function loadManualClientDetail(businessId, clientId) {
    const [clientRes, bookingsRes] = await Promise.all([
        supabase.from('manual_clients').select('*').eq('id', clientId).single(),
        supabase.from('bookings').select('*, services(name, price)')
            .eq('business_id', businessId).eq('manual_client_id', clientId)
            .order('booking_date', { ascending: false }),
    ]);
    return {
        client: clientRes.data || null,
        bookings: bookingsRes.data || [],
    };
}

export async function updateManualClient(clientId, { firstName, lastName, phone, email }) {
    const { error } = await supabase.from('manual_clients').update({
        first_name: firstName, last_name: lastName || '', phone: phone || '', email: email || '',
    }).eq('id', clientId);
    if (error) throw error;
}

export async function deleteManualClient(clientId) {
    const { error } = await supabase.from('manual_clients').delete().eq('id', clientId);
    if (error) throw error;
}
