// Catalogue pro (dambou.fr/pro/catalogue).
// Reproduit catalogue_screen.dart pour le cas standard (une seule activite,
// sans gestion d'ingredients -- module avance non couvert dans ce premier passage).
// Fichier en ASCII uniquement (contrainte Hostinger).

import { supabase } from '/pro/js/auth.js';

export const TVA_RATES = [
    { label: 'Non assujetti (0%)', value: 0.0 },
    { label: 'Super réduit (2,1%)', value: 2.1 },
    { label: 'Réduit (5,5%)', value: 5.5 },
    { label: 'Intermédiaire (10%)', value: 10.0 },
    { label: 'Normal (20%)', value: 20.0 },
];

// Categories du business (cas simple : parent_id null, hors multi-activite).
export async function loadCategories(businessId) {
    const { data, error } = await supabase
        .from('categories')
        .select('*')
        .eq('business_id', businessId)
        .is('parent_id', null)
        .order('sort_order');
    if (error) {
        console.error('Erreur chargement categories:', error);
        return [];
    }
    return data || [];
}

export async function loadProducts(businessId) {
    const { data, error } = await supabase
        .from('products')
        .select('*, categories(name)')
        .eq('business_id', businessId)
        .order('sort_order');
    if (error) {
        console.error('Erreur chargement produits:', error);
        return [];
    }
    return data || [];
}

export async function loadServices(businessId) {
    const { data, error } = await supabase
        .from('services')
        .select('*, categories(name)')
        .eq('business_id', businessId)
        .order('sort_order');
    if (error) {
        console.error('Erreur chargement services:', error);
        return [];
    }
    return data || [];
}

export async function createCategory(businessId, name, currentCount) {
    const { error } = await supabase.from('categories').insert({
        business_id: businessId,
        name: name,
        sort_order: currentCount,
    });
    if (error) throw error;
}

export async function deleteCategory(categoryId) {
    // Reproduit _deleteCategory() : supprime d'abord le contenu, puis la categorie.
    await supabase.from('products').delete().eq('category_id', categoryId);
    await supabase.from('services').delete().eq('category_id', categoryId);
    const { error } = await supabase.from('categories').delete().eq('id', categoryId);
    if (error) throw error;
}

// Redimensionne une image cote client avant upload (equivalent de FlutterImageCompress
// : max 800x800, JPEG qualite 0.85) pour eviter d'envoyer des fichiers trop lourds.
export function resizeImageFile(file) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const reader = new FileReader();
        reader.onload = (e) => { img.src = e.target.result; };
        reader.onerror = reject;
        img.onload = () => {
            const maxDim = 800;
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

// Upload une image dans le bucket business-assets, meme chemin que le mobile.
export async function uploadItemImage(businessId, itemId, file) {
    const blob = await resizeImageFile(file);
    const path = 'businesses/' + businessId + '/catalogue/' + itemId + '.jpg';
    const { error } = await supabase.storage.from('business-assets').upload(path, blob, {
        contentType: 'image/jpeg',
        upsert: true,
    });
    if (error) throw error;
    const { data } = supabase.storage.from('business-assets').getPublicUrl(path);
    return data.publicUrl + '?v=' + Date.now();
}

// Cree ou met a jour un produit ou un service (reproduit _save() de ItemEditorScreen).
export async function saveItem(isProduct, itemId, params) {
    const table = isProduct ? 'products' : 'services';
    const data = {
        business_id: params.businessId,
        category_id: params.categoryId || null,
        name: params.name,
        description: params.description || '',
        is_active: params.isActive,
        price: params.price,
    };
    if (params.imageUrl) data.image_url = params.imageUrl;

    if (!isProduct && params.duration) {
        data.duration = params.duration;
    }
    if (!isProduct) {
        data.max_participants = params.maxParticipants && params.maxParticipants > 1 ? params.maxParticipants : 1;
    }
    if (isProduct) {
        data.stock_qty = params.stockQty || 0;
        data.stock_alert = params.stockAlert != null ? params.stockAlert : 5;
        data.track_stock = !!params.trackStock;
    }
    if (params.tvaRate != null) {
        data.tva_rate = params.tvaRate;
    }

    if (itemId) {
        const { error } = await supabase.from(table).update(data).eq('id', itemId);
        if (error) throw error;
        return itemId;
    } else {
        let countQuery = supabase.from(table).select('id', { count: 'exact', head: true });
        countQuery = params.categoryId ? countQuery.eq('category_id', params.categoryId) : countQuery.is('category_id', null);
        const { count } = await countQuery;
        data.sort_order = count || 0;
        const { data: inserted, error } = await supabase.from(table).insert(data).select('id').single();
        if (error) throw error;
        return inserted.id;
    }
}

export async function updateItemImageUrl(isProduct, itemId, imageUrl) {
    const table = isProduct ? 'products' : 'services';
    const { error } = await supabase.from(table).update({ image_url: imageUrl }).eq('id', itemId);
    if (error) throw error;
}

export async function deleteItem(isProduct, itemId) {
    const table = isProduct ? 'products' : 'services';
    const { error } = await supabase.from(table).delete().eq('id', itemId);
    if (error) throw error;
}

// ------------------------------------------------------------
// STOCK (dambou.fr/pro/stock) -- reproduit stock_screen.dart
// ------------------------------------------------------------
export async function loadStockProducts(businessId) {
    const { data, error } = await supabase
        .from('products')
        .select('*, categories(name)')
        .eq('business_id', businessId)
        .eq('is_active', true)
        .order('name');
    if (error) {
        console.error('Erreur chargement stock:', error);
        return [];
    }
    return data || [];
}

export async function updateStockQty(productId, newQty) {
    const { error } = await supabase.from('products').update({ stock_qty: Math.max(newQty, 0) }).eq('id', productId);
    if (error) throw error;
}

// ------------------------------------------------------------
// INGREDIENTS (produits uniquement), reproduit catalogue_screen.dart
// ------------------------------------------------------------
export async function loadIngredientsForProduct(productId) {
    const { data, error } = await supabase.from('ingredients').select('*')
        .eq('product_id', productId).order('sort_order');
    if (error) { console.error('Erreur chargement ingredients:', error); return []; }
    return data || [];
}

// Tous les noms d'ingredients deja utilises quelque part dans le catalogue du
// business (pour proposer une reutilisation rapide en un clic).
export async function loadAllBusinessIngredientNames(businessId) {
    const { data: products } = await supabase.from('products').select('id').eq('business_id', businessId);
    const ids = (products || []).map((p) => p.id);
    if (ids.length === 0) return [];
    const { data } = await supabase.from('ingredients').select('name').in('product_id', ids);
    const names = new Set();
    (data || []).forEach((i) => { if (i.name) names.add(i.name); });
    return Array.from(names).sort();
}

// Remplace entierement les ingredients d'un produit (supprime puis reinsere,
// comme _saveIngredients() cote mobile).
export async function saveIngredients(productId, ingredients) {
    await supabase.from('ingredients').delete().eq('product_id', productId);
    if (ingredients.length === 0) return;
    const rows = ingredients.map((ing, i) => ({
        product_id: productId,
        name: ing.name,
        extra_price: ing.extra_price || 0,
        is_default: ing.is_default !== false,
        is_removable: ing.is_removable !== false,
        sort_order: i,
    }));
    const { error } = await supabase.from('ingredients').insert(rows);
    if (error) throw error;
}

export async function toggleTrackStock(productId, current) {
    const { error } = await supabase.from('products').update({ track_stock: !current }).eq('id', productId);
    if (error) throw error;
}
