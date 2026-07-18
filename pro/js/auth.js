// Client Supabase partage pour l'espace pro (dambou.fr/pro/*)
// Fichier en ASCII uniquement (contrainte Hostinger).

import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';

const SUPABASE_URL = 'https://unwrghiiocaztnecmpeh.supabase.co';
const SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVud3JnaGlpb2NhenRuZWNtcGVoIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ2Mjc4NTUsImV4cCI6MjA4MDIwMzg1NX0.m9s85OKGVTQbItxB8bHaCpfpvICRf5tWSztUyLvOeZw';

export const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

// Decode les entites HTML (accents francais) dans une chaine litterale ecrite
// en dur dans le code, avant assignation via .textContent (qui ne decode pas
// les entites, contrairement a innerHTML). N'utiliser QUE sur des chaines
// statiques que l'on ecrit soi-meme -- jamais sur une valeur venant de
// l'utilisateur, meme si la fonction est techniquement sans danger cote XSS
// (le div n'est jamais insere dans le document).
export function fr(str) {
    const div = document.createElement('div');
    div.innerHTML = str;
    return div.textContent;
}

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

// Recupere les modules actifs d'un business.
// Reproduit exactement la requete de pro_home_screen.dart :
// modules.select('*').eq('business_id', bizId).eq('is_enabled', true)
export async function getActiveModules(businessId) {
    const { data, error } = await supabase
        .from('modules')
        .select('*')
        .eq('business_id', businessId)
        .eq('is_enabled', true);
    if (error) {
        console.error('Erreur chargement modules:', error);
        return [];
    }
    return data || [];
}

// Deconnexion + redirection vers login.
export async function logout() {
    await supabase.auth.signOut();
    window.location.href = '/pro/login';
}
