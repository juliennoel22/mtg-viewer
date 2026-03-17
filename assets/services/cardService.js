export async function fetchAllCards(page = 1, setCode = '', artistId = '') {
    const params = new URLSearchParams({ page });
    if (setCode) params.append('set', setCode);
    if (artistId) params.append('artist', artistId);
    
    const response = await fetch(`/api/card/all?${params.toString()}`);
    if (!response.ok) throw new Error('Failed to fetch cards');
    return await response.json();
}

export async function fetchCard(uuid) {
    const response = await fetch(`/api/card/${uuid}`);
    if (response.status === 404) return null;
    if (!response.ok) throw new Error('Failed to fetch card');
    const card = await response.json();
    if (card.text) {
        card.text = card.text.replaceAll('\\n', '\n');
    }
    return card;
}

export async function searchCards(query, setCode = '', artistId = '') {
    const params = new URLSearchParams({ q: query });
    if (setCode) params.append('set', setCode);
    if (artistId) params.append('artist', artistId);
    
    const response = await fetch(`/api/card/search?${params.toString()}`);
    if (!response.ok) throw new Error('Failed to search cards');
    return await response.json();
}

export async function fetchSets() {
    const response = await fetch('/api/card/sets');
    if (!response.ok) throw new Error('Failed to fetch sets');
    return await response.json();
}

export async function fetchArtists() {
    const response = await fetch('/api/card/artists');
    if (!response.ok) throw new Error('Failed to fetch artists');
    return await response.json();
}
