// Reservations - liste globale (dambou.fr/pro/reservations).
// Reproduit la logique de chargement de bookings_screen.dart : 3 onglets
// (en attente / confirmees / toutes), plage de -7 a +30 jours.
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

export async function loadReservations(businessId, filterStatus) {
    const from = new Date();
    from.setDate(from.getDate() - 7);
    const to = new Date();
    to.setDate(to.getDate() + 30);
    const fromKey = from.toISOString().substring(0, 10);
    const toKey = to.toISOString().substring(0, 10);

    let query = supabase
        .from('bookings')
        .select('*, users!customer_id(id, first_name, last_name, phone), services(name, duration, price), employees!preferred_employee_id(id, first_name, last_name, color), booking_employees(employee_id, employees(id, first_name, last_name, color))')
        .eq('business_id', businessId)
        .gte('booking_date', fromKey)
        .lte('booking_date', toKey)
        .order('booking_date');

    if (filterStatus !== 'all') {
        query = query.eq('status', filterStatus);
    }

    const { data, error } = await query;
    if (error) {
        console.error('Erreur chargement reservations:', error);
        return [];
    }
    return data || [];
}
