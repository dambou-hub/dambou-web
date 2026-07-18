// Configuration business (dambou.fr/pro/business).
// Reproduit my_business_screen.dart pour les champs les plus utilises.
// Hors scope volontaire (avance/niche) : gestion des ingredients, capacite de
// preparation/commandes simultanees -- ajoutable plus tard si besoin.
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

export async function loadFullBusiness(businessId) {
    const { data, error } = await supabase.from('businesses').select('*').eq('id', businessId).single();
    if (error) throw error;
    return data;
}

function toSlug(name) {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

// Genere un slug unique a partir du nom (comme _generateSlug() dans l'app).
export async function generateUniqueSlug(name, businessId) {
    const base = toSlug(name);
    if (!base) return '';
    let candidate = base;
    let suffix = 2;
    while (true) {
        const { data } = await supabase.from('businesses').select('id').eq('slug', candidate).maybeSingle();
        if (!data || data.id === businessId) return candidate;
        candidate = base + '-' + suffix;
        suffix++;
    }
}

export async function saveBusinessInfo(businessId, params) {
    const updateData = {
        name: params.name,
        slug: params.slug || toSlug(params.name),
        description: params.description || '',
        phone: params.phone || '',
        email: params.email || '',
        website: params.website || '',
        slogan: params.slogan ? params.slogan.substring(0, 50) : null,
        whatsapp_enabled: !!params.whatsappEnabled,
        is_tva_assujetti: !!params.isTvaAssujetti,
        check_employee_conflicts: !!params.checkEmployeeConflicts,
        siret: params.siret || null,
        numero_tva: params.numeroTva || null,
        closure_message: params.closureEnabled && params.closureMessage ? params.closureMessage.trim() : null,
        capacity: params.capacity || 1,
        order_capacity: params.orderCapacity || 1,
        prep_time: params.prepTime || 15,
        updated_at: new Date().toISOString(),
    };
    const { error } = await supabase.from('businesses').update(updateData).eq('id', businessId);
    if (error) throw error;
}

export async function saveOpeningHours(businessId, hours) {
    const { error } = await supabase.from('businesses').update({ opening_hours: hours, updated_at: new Date().toISOString() }).eq('id', businessId);
    if (error) throw error;
}

export async function saveAddress(businessId, place) {
    const addressJson = {
        street: place.street, city: place.city, postal_code: place.postalCode,
        country: place.country, formatted: place.formatted, lat: place.lat, lng: place.lng,
    };
    const updateData = { address: addressJson, updated_at: new Date().toISOString() };
    if (place.lat && place.lng) updateData.location = 'POINT(' + place.lng + ' ' + place.lat + ')';
    const { error } = await supabase.from('businesses').update(updateData).eq('id', businessId);
    if (error) throw error;
}

// Redimensionne cote client avant upload (meme logique que catalogue.js).
function resizeImageFile(file, maxDim) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const reader = new FileReader();
        reader.onload = (e) => { img.src = e.target.result; };
        reader.onerror = reject;
        img.onload = () => {
            let w = img.width, h = img.height;
            if (w > maxDim || h > maxDim) {
                if (w > h) { h = Math.round(h * maxDim / w); w = maxDim; }
                else { w = Math.round(w * maxDim / h); h = maxDim; }
            }
            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            canvas.toBlob((blob) => resolve(blob), 'image/jpeg', 0.85);
        };
        img.onerror = reject;
        reader.readAsDataURL(file);
    });
}

async function uploadBusinessImage(businessId, file, filename, maxDim) {
    const blob = await resizeImageFile(file, maxDim);
    const path = 'businesses/' + businessId + '/' + filename;
    const { error } = await supabase.storage.from('business-assets').upload(path, blob, { contentType: 'image/jpeg', upsert: true });
    if (error) throw error;
    const { data } = supabase.storage.from('business-assets').getPublicUrl(path);
    const url = data.publicUrl + '?v=' + Date.now();
    const column = filename === 'logo.jpg' ? 'logo_url' : 'cover_url';
    await supabase.from('businesses').update({ [column]: url }).eq('id', businessId);
    return url;
}
export async function uploadLogo(businessId, file) {
    return uploadBusinessImage(businessId, file, 'logo.jpg', 800);
}
export async function uploadCover(businessId, file) {
    return uploadBusinessImage(businessId, file, 'cover.jpg', 1600);
}
