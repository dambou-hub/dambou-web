// Client Supabase partage pour l'espace pro (dambou.fr/pro/*)
// Fichier en ASCII uniquement (contrainte Hostinger).

import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';

const SUPABASE_URL = 'https://unwrghiiocaztnecmpeh.supabase.co';
const SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVud3JnaGlpb2NhenRuZWNtcGVoIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ2Mjc4NTUsImV4cCI6MjA4MDIwMzg1NX0.m9s85OKGVTQbItxB8bHaCpfpvICRf5tWSztUyLvOeZw';

export const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

// Verifie qu'une session existe. Redirige vers /pro/login si absente.
// Retourne la session si presente, sinon null (apres redirection).
export async function requireAuth() {
    const { data, error } = await supabase.auth.getSession();
    if (error || !data || !data.session) {
        window.location.href = '/pro/login';
        return null;
    }
    return data.session;
}

// Recupere le business associe a l'utilisateur connecte (owner_id = user.id).
// Retourne null si aucun business trouve.
export async function getBusinessForUser(userId) {
    const { data, error } = await supabase
        .from('businesses')
        .select('*')
        .eq('owner_id', userId)
        .maybeSingle();
    if (error) {
        console.error('Erreur chargement business:', error);
        return null;
    }
    return data;
}

// Deconnexion + redirection vers login.
export async function logout() {
    await supabase.auth.signOut();
    window.location.href = '/pro/login';
}
