// Planning pro (dambou.fr/pro/planning).
// Reproduit employee_planning_screen.dart + template_injection_service.dart pour la partie modules.
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

// Reservations sur une plage de dates (pour la vue semaine).
export async function loadBookingsForRange(businessId, fromKey, toKeyExclusive) {
    const { data, error } = await supabase
        .from('bookings')
        .select('*, booking_employees(employee_id), services(name, duration, price), users!customer_id(id, first_name, last_name, phone)')
        .eq('business_id', businessId)
        .gte('booking_date', fromKey)
        .lt('booking_date', toKeyExclusive)
        .neq('status', 'cancelled')
        .order('booking_date')
        .order('start_time');
    if (error) {
        console.error('Erreur chargement reservations (semaine):', error);
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

export function bookingPhone(booking) {
    const user = booking.users;
    return (user && user.phone) || booking.manual_customer_phone || '';
}

// Repartit des RDV qui se chevauchent en colonnes cote a cote (comme Google Calendar)
// au lieu de les superposer. items doit avoir {start, end} en minutes.
// Retourne un tableau parallele [{col, total}, ...] indexe comme items.
export function layoutOverlaps(items) {
    const withIdx = items.map((it, idx) => ({ start: it.start, end: it.end, idx: idx }));
    withIdx.sort((a, b) => a.start - b.start || a.end - b.end);

    const result = new Array(items.length);
    let cluster = [];
    let clusterMaxEnd = -1;

    function flush() {
        if (!cluster.length) return;
        const colEnds = [];
        cluster.forEach((e) => {
            let placed = false;
            for (let c = 0; c < colEnds.length; c++) {
                if (colEnds[c] <= e.start) {
                    colEnds[c] = e.end;
                    result[e.idx] = { col: c, total: 0 };
                    placed = true;
                    break;
                }
            }
            if (!placed) {
                colEnds.push(e.end);
                result[e.idx] = { col: colEnds.length - 1, total: 0 };
            }
        });
        cluster.forEach((e) => { result[e.idx].total = colEnds.length; });
        cluster = [];
    }

    withIdx.forEach((e) => {
        if (cluster.length === 0) {
            clusterMaxEnd = e.end;
        } else if (e.start >= clusterMaxEnd) {
            flush();
            clusterMaxEnd = e.end;
        } else {
            clusterMaxEnd = Math.max(clusterMaxEnd, e.end);
        }
        cluster.push(e);
    });
    flush();

    return result;
}

// Services actifs du business, pour le formulaire de nouvelle reservation.
export async function loadServices(businessId) {
    const { data, error } = await supabase
        .from('services')
        .select('*')
        .eq('business_id', businessId)
        .eq('is_active', true)
        .order('name');
    if (error) {
        console.error('Erreur chargement services:', error);
        return [];
    }
    return data || [];
}

// Recherche dans les fiches clients manuelles du business (autocompletion nom/telephone).
export async function searchManualClients(businessId, query) {
    if (!query || query.trim().length < 2) return [];
    const q = query.trim();
    const { data, error } = await supabase
        .from('manual_clients')
        .select('id, first_name, last_name, phone')
        .eq('business_id', businessId)
        .or('first_name.ilike.%' + q + '%,last_name.ilike.%' + q + '%,phone.ilike.%' + q + '%')
        .limit(5);
    if (error) {
        console.error('Erreur recherche clients:', error);
        return [];
    }
    return data || [];
}

// Cree une reservation manuelle (reproduit _NewBookingSheet._save()).
// Le RDV est cree directement confirme (le pro le saisit lui-meme).
export async function createBooking(params) {
    const insertData = {
        business_id: params.businessId,
        service_id: params.serviceId,
        booking_date: params.dateKey,
        start_time: params.startTime,
        end_time: params.endTime,
        status: 'confirmed',
        manual_customer_name: params.customerName,
        price: params.price || 0,
        preferred_employee_id: params.employeeId || null,
    };
    if (params.customerPhone) {
        insertData.manual_customer_phone = params.customerPhone;
        insertData.notes = 'Tel: ' + params.customerPhone;
    }
    if (params.manualClientId) insertData.manual_client_id = params.manualClientId;

    const { data, error } = await supabase.from('bookings').insert(insertData).select('id').single();
    if (error) throw error;

    if (params.employeeId) {
        await supabase.from('booking_employees').insert({ booking_id: data.id, employee_id: params.employeeId });
    }
    return data.id;
}

// Met a jour une reservation existante (utilise par le modal en mode edition).
// Ne touche au nom/telephone que si c'est une reservation manuelle (pas un vrai compte client app).
export async function updateBooking(bookingId, params) {
    const updateData = {
        service_id: params.serviceId,
        start_time: params.startTime,
        end_time: params.endTime,
        price: params.price || 0,
    };
    if (params.isManual) {
        updateData.manual_customer_name = params.customerName;
        if (params.customerPhone) {
            updateData.manual_customer_phone = params.customerPhone;
            updateData.notes = 'Tel: ' + params.customerPhone;
        }
    }
    const { error } = await supabase.from('bookings').update(updateData).eq('id', bookingId);
    if (error) throw error;

    await supabase.from('booking_employees').delete().eq('booking_id', bookingId);
    if (params.employeeId) {
        await supabase.from('booking_employees').insert({ booking_id: bookingId, employee_id: params.employeeId });
    }
}

export function bookingEmployeeIds(booking) {
    const rel = booking.booking_employees || [];
    return rel.map((r) => r.employee_id);
}

const FRENCH_DAYS = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

// Horaires d'ouverture du jour, depuis businesses.opening_hours (JSON par jour).
// Reproduit la structure de my_business_screen.dart : { isOpen, start, end }.
export function getDayHours(business, date) {
    const dayName = FRENCH_DAYS[date.getDay()];
    const fallback = { isOpen: true, start: '08:00', end: '20:00' };
    const oh = business && business.opening_hours;
    if (!oh || !oh[dayName]) return fallback;
    return oh[dayName];
}

export function timeToMinutes(t) {
    if (!t) return 0;
    const parts = t.split(':');
    return (parseInt(parts[0], 10) || 0) * 60 + (parseInt(parts[1], 10) || 0);
}

// Verifie si deplacer une reservation vers un employe/creneau entre en conflit
// avec une autre reservation deja assignee a cet employe ce jour-la.
export function hasConflict(dayBookings, employeeId, newStart, newEnd, excludeBookingId) {
    return dayBookings.some((b) => {
        if (b.id === excludeBookingId) return false;
        if (b.status === 'cancelled') return false;
        if (!bookingEmployeeIds(b).includes(employeeId)) return false;
        const s = timeToMinutes((b.start_time || '').substring(0, 5));
        const e = timeToMinutes((b.end_time || '').substring(0, 5));
        return newStart < e && newEnd > s;
    });
}

// Reassigne une reservation a un nouvel employe (glisser-deposer entre colonnes).
export async function updateBookingTime(bookingId, startTime, endTime) {
    const { error } = await supabase.from('bookings').update({ start_time: startTime, end_time: endTime }).eq('id', bookingId);
    if (error) throw error;
}

export async function reassignEmployee(bookingId, newEmployeeId) {
    await supabase.from('booking_employees').delete().eq('booking_id', bookingId);
    const { error } = await supabase.from('booking_employees').insert({ booking_id: bookingId, employee_id: newEmployeeId });
    if (error) throw error;
}

export async function confirmBooking(bookingId) {
    const { error } = await supabase.from('bookings').update({ status: 'confirmed' }).eq('id', bookingId);
    if (error) throw error;
}

export async function cancelBooking(bookingId) {
    const { error } = await supabase.from('bookings').update({ status: 'cancelled' }).eq('id', bookingId);
    if (error) throw error;
}

// Restaure un RDV marque no-show par erreur (reproduit le dialogue "Annuler le no-show").
export async function restoreNoShow(booking) {
    const bookingId = booking.id;
    const customerId = booking.customer_id;
    await supabase.from('bookings').update({ status: 'confirmed', no_show_reported_at: null }).eq('id', bookingId);
    if (!customerId) return;
    await supabase.from('client_no_shows').delete().eq('booking_id', bookingId);
    const { data: remaining } = await supabase
        .from('client_no_shows').select('id')
        .eq('customer_id', customerId).eq('business_id', booking.business_id);
    if ((remaining || []).length < 3) {
        await supabase.from('client_blocks').delete()
            .eq('customer_id', customerId).eq('business_id', booking.business_id);
    }
}

// Marque un client absent. Reproduit _handleNoShow() : incremente le compteur,
// bloque le client apres 3 no-shows, insere une notification.
export async function markNoShow(booking, businessId, businessName) {
    const bookingId = booking.id;
    const customerId = booking.customer_id;

    await supabase.from('bookings')
        .update({ status: 'no_show', no_show_reported_at: new Date().toISOString() })
        .eq('id', bookingId);

    if (!customerId) return { count: 0, blocked: false };

    try {
        await supabase.from('client_no_shows').insert({ customer_id: customerId, business_id: businessId, booking_id: bookingId });
    } catch (e) { /* ignore */ }

    const { data: noShows } = await supabase.from('client_no_shows')
        .select('id').eq('customer_id', customerId).eq('business_id', businessId);
    const count = (noShows || []).length;

    let title, message;
    if (count === 1) {
        title = 'Rendez-vous manque';
        message = 'Vous ne vous etes pas presente(e) a votre rendez-vous chez ' + businessName + '. ' +
            "Pensez a annuler au moins 2h a l'avance si vous ne pouvez pas venir.";
    } else if (count === 2) {
        title = '2eme absence non signalee';
        message = "C'est votre 2eme absence non annulee chez " + businessName + '. ' +
            'Attention : apres une 3eme absence, vous ne pourrez plus reserver en ligne chez ce professionnel.';
    } else {
        title = 'Reservations en ligne desactivees';
        message = 'Suite a 3 absences non annulees chez ' + businessName + ', ' +
            'les reservations en ligne ne sont plus disponibles. Contactez ' + businessName + ' directement pour prendre rendez-vous.';
        try {
            await supabase.from('client_blocks').upsert({ customer_id: customerId, business_id: businessId, reason: 'no_show' });
        } catch (e) { /* ignore */ }
    }

    try {
        await supabase.from('notifications').insert({
            user_id: customerId, title: title, message: message, type: 'no_show',
            data: { business_id: businessId, no_show_count: count }, is_read: false,
        });
    } catch (e) { /* ignore */ }

    return { count: count, blocked: count >= 3 };
}
