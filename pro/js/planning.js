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
const MONTH_NAMES = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

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
        .select('*, booking_employees(employee_id), services(name, duration, price, max_participants), users!customer_id(id, first_name, last_name, phone)')
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
        .select('*, booking_employees(employee_id), services(name, duration, price, max_participants), users!customer_id(id, first_name, last_name, phone)')
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
        price: params.price || 0,
        preferred_employee_id: params.employeeId || null,
    };
    if (params.customerId) {
        insertData.customer_id = params.customerId;
    } else {
        insertData.manual_customer_name = params.customerName;
        if (params.customerPhone) insertData.manual_customer_phone = params.customerPhone;
        if (params.manualClientId) insertData.manual_client_id = params.manualClientId;
    }

    const { data, error } = await supabase.from('bookings').insert(insertData).select('id').single();
    if (error) throw error;

    if (params.employeeId) {
        await supabase.from('booking_employees').insert({ booking_id: data.id, employee_id: params.employeeId });
    }
    return data.id;
}

// ------------------------------------------------------------
// ATELIERS (services a plusieurs participants, services.max_participants > 1)
// ------------------------------------------------------------
// Cree la "coquille" d'un atelier : une ligne bookings sans client, juste pour
// que le creneau apparaisse et bloque le planning des sa creation, avant meme
// la premiere inscription. Reproduit l'idee proposee par Jean : au moment de
// creer un RDV, si le service a plus d'une place, on ne demande pas de client.
export async function createWorkshopShell(params) {
    const insertData = {
        business_id: params.businessId,
        service_id: params.serviceId,
        booking_date: params.dateKey,
        start_time: params.startTime,
        end_time: params.endTime,
        status: 'confirmed',
        price: 0,
        preferred_employee_id: params.employeeId || null,
        is_workshop_shell: true,
    };
    const { data, error } = await supabase.from('bookings').insert(insertData).select('id').single();
    if (error) throw error;

    if (params.employeeId) {
        await supabase.from('booking_employees').insert({ booking_id: data.id, employee_id: params.employeeId });
    }
    return data.id;
}

// Inscrit un participant sur une session d'atelier existante (reprend service,
// date, heure et employe de la coquille -- seul le client change).
export async function addWorkshopParticipant(shellBooking, clientParams) {
    const insertData = {
        business_id: shellBooking.business_id,
        service_id: shellBooking.service_id,
        booking_date: shellBooking.booking_date,
        start_time: shellBooking.start_time,
        end_time: shellBooking.end_time,
        status: 'confirmed',
        price: (shellBooking.services && shellBooking.services.price) || 0,
        preferred_employee_id: shellBooking.preferred_employee_id || null,
        workshop_shell_id: shellBooking.id,
    };
    if (clientParams.customerId) {
        insertData.customer_id = clientParams.customerId;
    } else {
        insertData.manual_customer_name = clientParams.customerName;
        if (clientParams.customerPhone) insertData.manual_customer_phone = clientParams.customerPhone;
        if (clientParams.manualClientId) insertData.manual_client_id = clientParams.manualClientId;
    }

    const { data, error } = await supabase.from('bookings').insert(insertData).select('id').single();
    if (error) throw error;

    const empId = shellBooking.preferred_employee_id ||
        (bookingEmployeeIds(shellBooking).length ? bookingEmployeeIds(shellBooking)[0] : null);
    if (empId) {
        await supabase.from('booking_employees').insert({ booking_id: data.id, employee_id: empId });
    }
    return data.id;
}

// Retire un participant (annule sa reservation individuelle, la session
// elle-meme et les autres inscrits ne sont pas affectes).
export async function removeWorkshopParticipant(bookingId) {
    const { error } = await supabase.from('bookings').update({ status: 'cancelled' }).eq('id', bookingId);
    if (error) throw error;
}

// Regroupe une liste de reservations d'une journee : chaque coquille d'atelier
// avec ses inscrits (bookings actifs dont workshop_shell_id pointe vers elle).
// Renvoie { shell, participants } pour chaque atelier trouve.
export function groupWorkshopSessions(dayBookings) {
    const shells = dayBookings.filter((b) => b.is_workshop_shell);
    return shells.map((shell) => ({
        shell: shell,
        participants: dayBookings.filter((b) => b.workshop_shell_id === shell.id && b.status !== 'cancelled'),
    }));
}


// Ne touche au nom/telephone que si c'est une reservation manuelle (pas un vrai compte client app).
export async function updateBooking(bookingId, params) {
    // Auto-confirmation si le RDV etait en attente (reproduit _update() de
    // EditBookingSheet : "Gerer" un RDV en attente = le confirmer, il n'y a
    // pas de bouton "Confirmer" separe dans l'app).
    const wasPending = params.originalStatus === 'pending';
    const newStatus = wasPending ? 'confirmed' : (params.originalStatus || 'confirmed');

    const updateData = {
        service_id: params.serviceId,
        booking_date: params.dateKey,
        start_time: params.startTime,
        end_time: params.endTime,
        price: params.price || 0,
        status: newStatus,
    };
    if (params.customerId) {
        updateData.customer_id = params.customerId;
        updateData.manual_customer_name = null;
        updateData.manual_customer_phone = null;
        updateData.manual_client_id = null;
    } else {
        updateData.customer_id = null;
        updateData.manual_customer_name = params.customerName;
        updateData.manual_customer_phone = params.customerPhone || null;
        updateData.manual_client_id = params.manualClientId || null;
    }
    const { error } = await supabase.from('bookings').update(updateData).eq('id', bookingId);
    if (error) throw error;

    await supabase.from('booking_employees').delete().eq('booking_id', bookingId);
    if (params.employeeId) {
        await supabase.from('booking_employees').insert({ booking_id: bookingId, employee_id: params.employeeId });
    }

    // Notifications au client -- uniquement pour un vrai compte Dambou (customerId),
    // jamais pour une fiche manuelle. Une seule notif par sauvegarde, jamais les deux :
    // confirmation OU changement d'heure, pas les deux a la fois (meme logique que l'app).
    // La notification de confirmation mentionne en plus les changements d'horaire et/ou
    // d'employe par rapport a la demande initiale du client.
    if (params.customerId) {
        const wasConfirmed = wasPending && newStatus === 'confirmed';
        const originalTimeShort = (params.originalStartTime || '').substring(0, 5);
        const newTimeShort = (params.startTime || '').substring(0, 5);
        const timeChanged = originalTimeShort && originalTimeShort !== newTimeShort;
        const employeeChanged = params.originalPreferredEmployeeId && params.employeeId &&
            params.originalPreferredEmployeeId !== params.employeeId;

        try {
            if (wasConfirmed) {
                let message = (params.businessName || 'Le professionnel') + ' a confirmé votre RDV - ' +
                    (params.serviceName || 'votre rendez-vous') + ' à ' + newTimeShort + ' le ' + params.dateKey + '.';
                const changes = [];
                if (timeChanged) changes.push("l'horaire a été ajusté (initialement demandé à " + originalTimeShort + ')');
                if (employeeChanged && params.assignedEmployeeName) changes.push("c'est " + params.assignedEmployeeName + ' qui s\'occupera de vous');
                if (changes.length) message += ' A noter : ' + changes.join(', ') + '.';

                await supabase.from('notifications').insert({
                    user_id: params.customerId,
                    title: 'Rendez-vous confirmé',
                    message: message,
                    type: 'booking_confirmed',
                    data: { booking_id: bookingId },
                    is_read: false,
                });
            } else if (timeChanged) {
                await supabase.from('notifications').insert({
                    user_id: params.customerId,
                    title: 'Heure de RDV modifiée',
                    message: (params.businessName || 'Le professionnel') + ' a modifié votre RDV - ' +
                        (params.serviceName || 'votre rendez-vous') + ' est maintenant à ' + newTimeShort + ' le ' + params.dateKey + '.',
                    type: 'booking_updated',
                    data: { booking_id: bookingId },
                    is_read: false,
                });
            }
        } catch (e) { /* ignore */ }
    }
}

// Recherche unifiee client (comptes Dambou lies a ce business + fiches manuelles).
// Reproduit client_search_screen.dart, sans la partie scan (pas pertinente sur web).
export async function searchClients(businessId, query) {
    const q = (query || '').trim();
    if (q.length < 2) return { dambou: [], manual: [] };

    let dambou = [];
    try {
        const [bookingIds, orderIds, txIds, subIds] = await Promise.all([
            supabase.from('bookings').select('customer_id').eq('business_id', businessId),
            supabase.from('orders').select('customer_id').eq('business_id', businessId),
            supabase.from('transactions').select('customer_id').eq('business_id', businessId),
            supabase.from('customer_subscriptions').select('customer_id').eq('business_id', businessId),
        ]);
        const ids = new Set();
        [bookingIds.data, orderIds.data, txIds.data, subIds.data].forEach((list) => {
            (list || []).forEach((r) => { if (r.customer_id) ids.add(r.customer_id); });
        });
        if (ids.size) {
            const { data } = await supabase
                .from('users')
                .select('id, first_name, last_name, phone, email')
                .in('id', Array.from(ids))
                .or('first_name.ilike.%' + q + '%,last_name.ilike.%' + q + '%,phone.ilike.%' + q + '%')
                .limit(10);
            dambou = data || [];
        }
    } catch (e) {
        console.error('Erreur recherche clients Dambou:', e);
    }

    let manual = [];
    try {
        const { data } = await supabase
            .from('manual_clients')
            .select('*')
            .eq('business_id', businessId)
            .or('first_name.ilike.%' + q + '%,last_name.ilike.%' + q + '%,phone.ilike.%' + q + '%,email.ilike.%' + q + '%')
            .order('first_name')
            .limit(10);
        manual = data || [];
    } catch (e) {
        console.error('Erreur recherche clients manuels:', e);
    }

    return { dambou: dambou, manual: manual };
}

// Cree une fiche client manuelle. Si l'email correspond a un compte Dambou existant,
// la fiche est liee automatiquement (reproduit _ManualClientSheet._save()).
export async function createManualClient(businessId, { firstName, lastName, phone, email }) {
    let userId = null;
    if (email) {
        try {
            const { data } = await supabase.from('users').select('id').eq('email', email).maybeSingle();
            userId = (data && data.id) || null;
        } catch (e) { /* ignore */ }
    }
    const { data, error } = await supabase.from('manual_clients').insert({
        business_id: businessId,
        first_name: firstName,
        last_name: lastName || '',
        phone: phone || '',
        email: email || '',
        user_id: userId,
    }).select().single();
    if (error) throw error;
    return data;
}

// RDV en attente de confirmation, pour le tableau de bord (reproduit la requete
// de pro_home_screen.dart : status='pending', avec employe prefere si renseigne).
export async function loadPendingBookings(businessId, limit) {
    const { data, error } = await supabase
        .from('bookings')
        .select('*, booking_employees(employee_id), services(name, duration), users!customer_id(id, first_name, last_name, phone), employees!preferred_employee_id(first_name, last_name, color)')
        .eq('business_id', businessId)
        .eq('status', 'pending')
        .order('booking_date')
        .limit(limit || 10);
    if (error) {
        console.error('Erreur chargement RDV en attente:', error);
        return [];
    }
    return data || [];
}

// Verifie si confirmer ce RDV entrerait en conflit avec un autre RDV deja
// confirme du meme employe ce jour-la. L'app mobile ne fait AUCUNE verification
// avant de confirmer depuis le tableau de bord -- c'est une amelioration web.
export async function checkConflictForPendingBooking(businessId, booking) {
    const empId = booking.preferred_employee_id ||
        (bookingEmployeeIds(booking).length ? bookingEmployeeIds(booking)[0] : null);
    if (!empId) return null;

    const dayBookings = await loadBookingsForDay(businessId, booking.booking_date);
    const start = timeToMinutes((booking.start_time || '').substring(0, 5));
    const end = timeToMinutes((booking.end_time || '').substring(0, 5)) || (start + 30);

    if (hasConflict(dayBookings, empId, start, end, booking.id)) {
        const employee = booking.employees;
        return (employee ? ((employee.first_name || '') + ' ' + (employee.last_name || '')).trim() : 'Cet employé') +
            ' a déjà un rendez-vous confirmé sur ce créneau.';
    }
    return null;
}

export function bookingEmployeeIds(booking) {
    const rel = booking.booking_employees || [];
    if (rel.length > 0) return rel.map((r) => r.employee_id);
    // Une reservation prise par le client depuis l'app (booking_screen.dart) ne renseigne
    // que preferred_employee_id, jamais de ligne booking_employees -- celle-ci n'est creee
    // que quand le pro cree lui-meme le RDV (employee_planning_screen.dart). Sans ce
    // fallback, ces reservations n'apparaissent nulle part dans le planning, meme confirmees.
    if (booking.preferred_employee_id) return [booking.preferred_employee_id];
    return [];
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
// Les reservations liees a un atelier (coquille ou inscrit) sont exclues :
// plusieurs inscrits partagent volontairement le meme employe/creneau, ce
// n'est pas un conflit.
export function hasConflict(dayBookings, employeeId, newStart, newEnd, excludeBookingId) {
    return dayBookings.some((b) => {
        if (b.id === excludeBookingId) return false;
        if (b.status === 'cancelled') return false;
        if (b.is_workshop_shell || b.workshop_shell_id) return false;
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
        title = 'Rendez-vous manqué';
        message = 'Vous ne vous êtes pas présenté(e) à votre rendez-vous chez ' + businessName + '. ' +
            "Pensez à annuler au moins 2h à l'avance si vous ne pouvez pas venir.";
    } else if (count === 2) {
        title = '2ème absence non signalée';
        message = "C'est votre 2ème absence non annulée chez " + businessName + '. ' +
            'Attention : après une 3ème absence, vous ne pourrez plus réserver en ligne chez ce professionnel.';
    } else {
        title = 'Réservations en ligne désactivées';
        message = 'Suite à 3 absences non annulées chez ' + businessName + ', ' +
            'les réservations en ligne ne sont plus disponibles. Contactez ' + businessName + ' directement pour prendre rendez-vous.';
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
