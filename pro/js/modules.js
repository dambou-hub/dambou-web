// Gestion des modules (dambou.fr/pro/modules).
// Reproduit modules_screen.dart. Exclus volontairement : terminal (appairage
// Bluetooth physique), stripe_payment (onboarding Stripe Connect complet),
// multi_activity (migration de donnees, notre catalogue ne le gere pas).
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

export const ALL_MODULES = [
    { type: 'booking', emoji: '\u{1F4C5}', name: 'Reservations', desc: 'Les clients prennent rendez-vous en ligne. Vous recevez une notification et confirmez.', category: 'Client' },
    { type: 'orders', emoji: '\u{1F6D2}', name: 'Module Commandes', desc: "Activez la prise de commandes (click & collect). Un sous-reglage permet d'autoriser les commandes en ligne depuis l'app client.", category: 'Client' },
    { type: 'pos', emoji: '\u{1F4B3}', name: 'Caisse / POS', desc: 'Encaissez vos clients, generez des tickets et suivez vos ventes.', category: 'Gestion' },
    { type: 'stock', emoji: '\u{1F4E6}', name: 'Gestion du stock', desc: 'Suivez vos niveaux de stock, recevez des alertes de rupture.', category: 'Gestion' },
    { type: 'employees', emoji: '\u{1F465}', name: 'Equipe & Employes', desc: 'Gerez vos employes, attribuez des services et suivez les plannings.', category: 'Gestion' },
    { type: 'session_notes', emoji: '\u{1F4CB}', name: 'Notes de seance', desc: 'Redigez un compte rendu apres chaque seance. Historique complet par client, visible uniquement par vous.', category: 'Gestion' },
    { type: 'loyalty', emoji: '\u{1F381}', name: 'Programme de fidelite', desc: 'Recompensez vos clients fideles avec des points et des avantages.', category: 'Marketing' },
    { type: 'subscriptions', emoji: '\u{1F504}', name: 'Forfaits', desc: 'Vendez des seances en avance a vos clients.', category: 'Marketing' },
    { type: 'ai_assistant', emoji: '\u{1F916}', name: 'Assistant IA', desc: 'Vos clients posent leurs questions en langage naturel et recoivent une reponse instantanee.', category: 'Marketing' },
    { type: 'stats', emoji: '\u{1F4CA}', name: 'Statistiques avancees', desc: 'Analysez vos performances : CA, clients, services populaires.', category: 'Analyse' },
];

export async function loadModuleStates(businessId) {
    const { data, error } = await supabase.from('modules').select('*').eq('business_id', businessId);
    if (error) { console.error('Erreur chargement modules:', error); return {}; }
    const states = {};
    (data || []).forEach((m) => { states[m.module_type] = m; });
    return states;
}

export async function toggleModule(businessId, moduleType, enabled) {
    const { data: existing } = await supabase.from('modules').select('id')
        .eq('business_id', businessId).eq('module_type', moduleType).maybeSingle();
    if (existing) {
        const { error } = await supabase.from('modules')
            .update({ is_enabled: enabled, updated_at: new Date().toISOString() })
            .eq('business_id', businessId).eq('module_type', moduleType);
        if (error) throw error;
    } else {
        const { error } = await supabase.from('modules').insert({ business_id: businessId, module_type: moduleType, is_enabled: enabled });
        if (error) throw error;
    }
}

export async function saveAiContext(businessId, aiContext) {
    const { error } = await supabase.from('businesses').update({ ai_context: aiContext }).eq('id', businessId);
    if (error) throw error;
}

export async function toggleOnlineOrders(businessId, enabled) {
    const { error } = await supabase.from('modules')
        .update({ online_enabled: enabled, updated_at: new Date().toISOString() })
        .eq('business_id', businessId).eq('module_type', 'orders');
    if (error) throw error;
}
