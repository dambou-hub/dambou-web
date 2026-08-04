// Employes (dambou.fr/pro/employees).
// Reproduit employees_screen.dart (cote pro uniquement -- pas d'espace web
// dedie pour l'employe lie, qui continue d'utiliser l'app mobile).
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

export const EMPLOYEE_COLORS = [
    '#00BFA5', '#E53E3E', '#DD6B20', '#D69E2E',
    '#38A169', '#3182CE', '#805AD5', '#D53F8C',
];

export async function loadEmployees(businessId) {
    const { data, error } = await supabase.from('employees').select('*')
        .eq('business_id', businessId).order('first_name');
    if (error) { console.error('Erreur chargement employes:', error); return []; }
    return data || [];
}

export async function saveEmployee(businessId, employeeId, params) {
    const data = {
        business_id: businessId,
        first_name: params.firstName,
        last_name: params.lastName || '',
        phone: params.phone || '',
        email: params.email || '',
        color: params.color,
        can_see_all_planning: !!params.canSeeAllPlanning,
        is_active: true,
    };
    if (employeeId) {
        const { error } = await supabase.from('employees').update(data).eq('id', employeeId);
        if (error) throw error;
        return employeeId;
    }
    const { data: created, error } = await supabase.from('employees').insert(data).select('id').single();
    if (error) throw error;
    return created.id;
}

export async function toggleEmployeeActive(id, current) {
    const { error } = await supabase.from('employees').update({ is_active: !current }).eq('id', id);
    if (error) throw error;
}

export async function deleteEmployee(id) {
    const { error } = await supabase.from('employees').delete().eq('id', id);
    if (error) throw error;
}

// Invite ou lie directement un compte Dambou existant (meme Edge Function
// que l'app mobile -- si un compte existe deja avec cet email, liaison
// immediate ; sinon, email d'invitation envoye).
export async function inviteEmployee(employeeId, email, businessName) {
    const { data, error } = await supabase.functions.invoke('invite-employee', {
        body: { employeeId: employeeId, email: email, businessName: businessName },
    });
    if (error) throw error;
    return (data && data.status) || 'invited';
}
