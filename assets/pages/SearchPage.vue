<script setup>
import { onMounted, ref, watch } from 'vue';
import { searchCards, fetchSets, fetchArtists } from '../services/cardService';

const query = ref('');
const sets = ref([]);
const artists = ref([]);
const selectedSet = ref('');
const selectedArtist = ref('');
const cards = ref([]);
const loadingCards = ref(false);
let timeout = null;

async function handleSearch() {
    if (query.value.length < 3) {
        cards.value = [];
        return;
    }

    loadingCards.value = true;
    try {
        cards.value = await searchCards(query.value, selectedSet.value, selectedArtist.value);
    } catch (error) {
        console.error('Search failed:', error);
    } finally {
        loadingCards.value = false;
    }
}

onMounted(async () => {
    const [setsRes, artistsRes] = await Promise.all([fetchSets(), fetchArtists()]);
    sets.value = setsRes;
    artists.value = artistsRes;
});

watch([query, selectedSet, selectedArtist], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        handleSearch();
    }, 300);
});
</script>

<template>
    <div class="search-page">
        <h1>Rechercher une Carte</h1>
        
        <div class="filters">
            <div class="search-input">
                <input 
                    v-model="query" 
                    type="text" 
                    placeholder="Entrez au moins 3 caractères..."
                    class="form-control"
                >
            </div>
            <div class="filter-item">
                <select v-model="selectedSet" class="form-control">
                    <option value="">Tous les sets</option>
                    <option v-for="set in sets" :key="set" :value="set">{{ set }}</option>
                </select>
            </div>
            <div class="filter-item">
                <select v-model="selectedArtist" class="form-control">
                    <option value="">Tous les artistes</option>
                    <option v-for="artist in artists" :key="artist.id" :value="artist.id">{{ artist.name }}</option>
                </select>
            </div>
        </div>

        <div class="card-list">
            <div v-if="loadingCards">Chargement...</div>
            <div v-else-if="cards.length > 0">
                <div class="card-result" v-for="card in cards" :key="card.id">
                    <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                        {{ card.name }} <span class="set-code">[{{ card.setCode }}]</span> 
                        <span v-if="card.artist" class="artist-name"> by {{ card.artist.name }}</span>
                        <span>({{ card.uuid }})</span>
                    </router-link>
                </div>
            </div>
            <div v-else-if="query.length >= 3">
                Aucun résultat pour "{{ query }}" 
                <span v-if="selectedSet">dans le set {{ selectedSet }}</span>
                <span v-if="selectedArtist">pour cet artiste</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.search-page {
    padding: 20px;
}
.filters {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.search-input {
    flex-grow: 1;
    min-width: 250px;
}
.filter-item {
    min-width: 150px;
}
.filters .form-control {
    width: 100%;
    padding: 10px;
    font-size: 1.1rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.card-result {
    margin-bottom: 8px;
    padding: 10px;
    border-bottom: 1px solid #eee;
}
.card-result span {
    color: #666;
    font-size: 0.9rem;
}
.set-code {
    font-weight: bold;
    color: #444;
}
.artist-name {
    font-style: italic;
    color: #666;
}
</style>
