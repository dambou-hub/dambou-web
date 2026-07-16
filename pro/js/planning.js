// Planning pro (dambou.fr/pro/planning) - vue jour par jour.
// Reproduit employee_planning_screen.dart (lecture seule pour ce premier passage).
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

// Format YYYY-MM-DD (heure locale, pas UTC)
export function toDateKey(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + d;
}

const DAY_NAMES = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
const MONTH_NAMES = ['janvier', 'fevrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'decembre'];

export function formatDateLong(date) {
    return DAY_NAMES[date.getDay()] + ' ' + date.getDate() + ' ' + MONTH_NAMES[date.getMonth()] + ' ' + date.getFullYear();
}

// Employes actifs du business, tries par prenom (comme le mobile)
export async function loadEmployees(businessId) {
    const { data, error } = await supabase
        .from('employees')
        .select('*')
        .eq('business_id', businessId)
        .eq('is_active', true)
        .order('first_name');
    if (error) {
        console.error('Erreur chargement employes:', error);
        return [];
    }
    return data || [];
}

// Reservations d'un jour donne (hors annulees), avec employe(s)/service/client lies.
// Reproduit la meme jointure que le mobile, filtree sur un seul jour au lieu
// d'une plage de 30 jours (le mobile precharge pour le swipe, le web navigue page par page).
export async function loadBookingsForDay(businessId, dateKey) {
    const { data, error } = await supabase
        .from('bookings')
        .select('*, booking_employees(employee_id), services(name, duration, price), users!customer_id(id, first_name, last_name, phone)')
        .eq('business_id', businessId)
        .eq('booking_date', dateKey)
        .neq('status', 'cancelled')
        .order('start_time');
    if (error) {
        console.error('Erreur chargement reservations:', error);
        return [];
    }
    return data || [];
}

export function clientName(booking) {
    const user = booking.users;
    if (user) {
        const name = ((user.first_name || '') + ' ' + (user.last_name || '')).trim();
        if (name) return name;
    }
    return booking.manual_customer_name || 'Client';
}

export function bookingEmployeeIds(booking) {
    const rel = booking.booking_employees || [];
    return rel.map((r) => r.employee_id);
}
