// Inscription pro (dambou.fr/pro/inscription).
// Reproduit fidelement pro_onboarding_screen.dart + template_injection_service.dart
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

// ------------------------------------------------------------
// Pays / devises / indicatifs (miroir de country_config.dart)
// ------------------------------------------------------------
export const COUNTRIES = [
    { code: 'france', name: 'France', flag: 'FR', currencyCode: 'EUR', phonePrefix: '+33' },
    { code: 'maroc', name: 'Maroc', flag: 'MA', currencyCode: 'MAD', phonePrefix: '+212' },
    { code: 'belgique', name: 'Belgique', flag: 'BE', currencyCode: 'EUR', phonePrefix: '+32' },
    { code: 'suisse', name: 'Suisse', flag: 'CH', currencyCode: 'CHF', phonePrefix: '+41' },
    { code: 'senegal', name: 'Senegal', flag: 'SN', currencyCode: 'XOF', phonePrefix: '+221' },
    { code: 'cotedivoire', name: "Cote d'Ivoire", flag: 'CI', currencyCode: 'XOF', phonePrefix: '+225' },
];

// ------------------------------------------------------------
// ETAPE 1 - Creation du compte (auth.signUp + upsert users)
// Reproduit _Step1Account._submit() -- OTP desactive, comme dans l'app actuelle.
// ------------------------------------------------------------
export async function createAccount({ firstName, lastName, email, phone, phonePrefix, password }) {
    const { data, error } = await supabase.auth.signUp({
        email: email.trim(),
        password: password,
        options: {
            data: {
                first_name: firstName.trim(),
                last_name: lastName.trim(),
                role: 'business_owner',
            },
        },
    });

    if (error) throw error;
    if (!data.user) throw new Error('Compte non cree.');

    try {
        await supabase.from('users').upsert({
            id: data.user.id,
            email: email.trim(),
            first_name: firstName.trim(),
            last_name: lastName.trim(),
            phone: phonePrefix + phone.trim(),
            role: 'business_owner',
            onboarding_step: 1,
        }, { onConflict: 'id' });
    } catch (e) {
        // Insertion echouee mais auth OK -- on continue quand meme (comme le mobile).
        console.error('Erreur upsert users:', e);
    }

    return data.user;
}

// ------------------------------------------------------------
// ETAPE 2 - Chargement des templates business (business_templates)
// Reproduit _Step3Activity._loadTemplates()
// ------------------------------------------------------------
export async function loadTemplates() {
    const { data, error } = await supabase
        .from('business_templates')
        .select('id, name, icon, description, color, color2, accent_color, category, is_active, sort_order')
        .eq('is_active', true)
        .order('sort_order', { ascending: true, nullsFirst: false });

    if (error) {
        console.error('Erreur chargement templates:', error);
        return [];
    }
    return data || [];
}

// ------------------------------------------------------------
// ETAPE 3 - Adresse (OpenStreetMap Nominatim -- pas de cle API)
// Reproduit places_service.dart
// ------------------------------------------------------------
const NOMINATIM_URL = 'https://nominatim.openstreetmap.org';

export async function searchAddress(query) {
    if (!query || query.trim().length < 3) return [];
    try {
        const url = `${NOMINATIM_URL}/search?q=${encodeURIComponent(query.trim())}` +
            '&format=json&addressdetails=1&limit=6&countrycodes=fr,ma,be,ch,sn,ci&accept-language=fr';
        const res = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
        if (!res.ok) return [];
        const data = await res.json();
        return data.map(nominatimToPlace);
    } catch (e) {
        console.error('Erreur recherche adresse:', e);
        return [];
    }
}

export async function reverseGeocode(lat, lng) {
    try {
        const url = `${NOMINATIM_URL}/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1&accept-language=fr`;
        const res = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
        if (!res.ok) return null;
        const data = await res.json();
        return nominatimToPlace(data);
    } catch (e) {
        console.error('Erreur reverse geocoding:', e);
        return null;
    }
}

function nominatimToPlace(item) {
    const address = item.address || {};
    const houseNumber = address.house_number || '';
    const road = address.road || address.pedestrian || address.footway || '';
    const city = address.city || address.town || address.village || address.municipality || '';
    const postcode = address.postcode || '';
    const country = address.country || '';
    const street = houseNumber ? `${houseNumber} ${road}`.trim() : road;

    const parts = [];
    if (street) parts.push(street);
    if (postcode && city) parts.push(`${postcode} ${city}`);
    else if (city) parts.push(city);
    if (country && country !== 'France') parts.push(country);

    return {
        formatted: parts.length ? parts.join(', ') : (item.display_name || ''),
        lat: parseFloat(item.lat) || 0,
        lng: parseFloat(item.lon) || 0,
        street: street,
        city: city,
        postalCode: postcode,
        country: country,
    };
}

// ------------------------------------------------------------
// ETAPE 4 - Creation du business + injection du template
// Reproduit template_injection_service.dart EXACTEMENT
// ------------------------------------------------------------
function slugify(name) {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

export async function createBusinessWithTemplate({ ownerId, businessName, templateId, phone, email, place, currencyCode }) {
    // Charger category + allow_customer_ingredients depuis le template
    const { data: tplInfo, error: tplInfoError } = await supabase
        .from('business_templates')
        .select('category, allow_customer_ingredients')
        .eq('id', templateId)
        .single();
    if (tplInfoError) throw tplInfoError;

    const category = tplInfo.category || 'autre';
    const allowIngredients = tplInfo.allow_customer_ingredients || false;
    const slug = slugify(businessName);

    const bizInsert = {
        owner_id: ownerId,
        name: businessName,
        slug: slug,
        phone: phone,
        email: email,
        category: category,
        allow_customer_ingredients: allowIngredients,
        is_active: true,
    };

    const { data: biz, error: bizError } = await supabase
        .from('businesses').insert(bizInsert).select('id').single();
    if (bizError) throw bizError;

    const businessId = biz.id;

    // template_id (best-effort, comme le mobile)
    try {
        await supabase.from('businesses').update({ template_id: templateId }).eq('id', businessId);
    } catch (e) { /* colonne peut-etre absente -- ignore */ }

    // Adresse + devise (best-effort, comme le mobile)
    try {
        const updateData = {
            phone: phone,
            currency_code: currencyCode,
            updated_at: new Date().toISOString(),
        };
        if (place) {
            updateData.address = {
                street: place.street, city: place.city, postal_code: place.postalCode,
                country: place.country, formatted: place.formatted, lat: place.lat, lng: place.lng,
            };
        }
        await supabase.from('businesses').update(updateData).eq('id', businessId);
    } catch (e) {
        console.error('Erreur mise a jour adresse/devise:', e);
    }

    await injectTemplate(businessId, templateId);

    return businessId;
}

async function injectTemplate(businessId, templateId) {
    const { data: tpl, error } = await supabase
        .from('business_templates')
        .select('modules, vocabulary, default_categories, default_services, default_products')
        .eq('id', templateId)
        .single();
    if (error) throw error;

    const modules = tpl.modules || [];
    const vocabulary = tpl.vocabulary || {};
    const categories = tpl.default_categories || [];
    const services = tpl.default_services || [];
    const products = tpl.default_products || [];

    await injectModules(businessId, modules, vocabulary);
    const categoryIds = await injectCategories(businessId, categories);
    if (services.length) await injectServices(businessId, services, categoryIds);
    if (products.length) await injectProducts(businessId, products, categoryIds);
}

async function injectModules(businessId, modules, vocabulary) {
    if (!modules.length) return;
    const rows = modules.map((moduleType) => ({
        business_id: businessId,
        module_type: moduleType,
        is_enabled: true,
        subscription_type: 'free',
        vocabulary: vocabulary,
    }));
    const { error } = await supabase.from('modules').insert(rows);
    if (error) console.error('Erreur injection modules:', error);
}

async function injectCategories(businessId, categories) {
    const categoryIds = {};
    for (let i = 0; i < categories.length; i++) {
        const cat = categories[i];
        try {
            const { data, error } = await supabase.from('categories').insert({
                business_id: businessId,
                name: cat.name,
                sort_order: i,
                is_active: true,
            }).select('id').single();
            if (error) throw error;
            categoryIds[cat.name] = data.id;
        } catch (e) {
            console.error(`Erreur categorie ${cat.name}:`, e);
        }
    }
    return categoryIds;
}

async function injectServices(businessId, services, categoryIds) {
    for (let i = 0; i < services.length; i++) {
        const s = services[i];
        try {
            const { error } = await supabase.from('services').insert({
                business_id: businessId,
                name: s.name,
                description: s.description || '',
                duration: s.duration_minutes || 30,
                price: s.price || 0,
                category_id: categoryIds[s.category] || null,
                is_active: true,
                sort_order: i,
            });
            if (error) throw error;
        } catch (e) {
            console.error(`Erreur service ${s.name}:`, e);
        }
    }
}

async function injectProducts(businessId, products, categoryIds) {
    for (let i = 0; i < products.length; i++) {
        const p = products[i];
        try {
            const { error } = await supabase.from('products').insert({
                business_id: businessId,
                name: p.name,
                description: p.description || '',
                price: p.price || 0,
                category_id: categoryIds[p.category] || null,
                has_ingredients: p.has_ingredients || false,
                is_active: true,
                sort_order: i,
            });
            if (error) throw error;
        } catch (e) {
            console.error(`Erreur produit ${p.name}:`, e);
        }
    }
}
