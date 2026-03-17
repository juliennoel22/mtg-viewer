<script setup>
import { onMounted, ref, watch } from 'vue';
import { fetchAllCards, fetchSets, fetchArtists } from '../services/cardService';

const cards = ref([]);
const sets = ref([]);
const artists = ref([]);
const selectedSet = ref('');
const selectedArtist = ref('');
const page = ref(1);
const loading = ref(false);

async function loadCards(pageNum) {
    loading.value = true;
    page.value = pageNum;
    cards.value = await fetchAllCards(page.value, selectedSet.value, selectedArtist.value);
    loading.value = false;
}

onMounted(async () => {
    const [setsRes, artistsRes] = await Promise.all([fetchSets(), fetchArtists()]);
    sets.value = setsRes;
    artists.value = artistsRes;
    loadCards(1);
});

watch([selectedSet, selectedArtist], () => {
    loadCards(1);
});

</script>

<template>
    <h1>Toutes les cartes</h1>

    <div class="filters">
        <div class="search-link">
            <router-link :to="{ name: 'search-cards' }">Rechercher une carte spécifique</router-link>
        </div>
        
        <div class="filter-items">
            <div class="filter-item">
                <label for="set-select">Set : </label>
                <select id="set-select" v-model="selectedSet">
                    <option value="">Tous les sets</option>
                    <option v-for="set in sets" :key="set" :value="set">{{ set }}</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="artist-select">Artiste : </label>
                <select id="artist-select" v-model="selectedArtist">
                    <option value="">Tous les artistes</option>
                    <option v-for="artist in artists" :key="artist.id" :value="artist.id">{{ artist.name }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="pagination">
        <button @click="loadCards(page - 1)" :disabled="page <= 1">Précédent</button>
        <span>Page {{ page }}</span>
        <button @click="loadCards(page + 1)" :disabled="cards.length < 100">Suivant</button>
    </div>

    <div class="card-list">
        <p v-if="loading">Chargement...</p>
        <div v-else class="card-result" v-for="card in cards" :key="card.id">
            <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                {{ card.name }} <span class="set-code">[{{ card.setCode }}]</span> 
                <span v-if="card.artist" class="artist-name"> by {{ card.artist.name }}</span>
                <span>({{ card.uuid }})</span>
            </router-link>
        </div>
    </div>
</template>

<style scoped>
.filters {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 20px;
    flex-wrap: wrap;
}
.filter-items {
    display: flex;
    gap: 15px;
}
.filter-item select {
    padding: 5px;
    border-radius: 4px;
}
.pagination {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    align-items: center;
}
.card-result {
    margin-bottom: 5px;
}
.search-link {
    margin-bottom: 0px;
}
.set-code {
    font-weight: bold;
    color: #444;
}
.artist-name {
    font-style: italic;
    color: #666;
    margin-right: 5px;
}
</style>
