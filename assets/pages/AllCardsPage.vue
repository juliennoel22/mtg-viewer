<script setup>
import { onMounted, ref } from 'vue';

const cards = ref([]);
const page = ref(1);
const loading = ref(false);

async function loadCards(pageNum) {
    loading.value = true;
    page.value = pageNum;
    const response = await fetch(`/api/card/all?page=${pageNum}`);
    cards.value = await response.json();
    loading.value = false;
}

onMounted(() => {
    loadCards(1);
});

</script>

<template>
    <h1>Toutes les cartes</h1>

    <div class="pagination">
        <button @click="loadCards(page - 1)" :disabled="page <= 1">Précédent</button>
        <span>Page {{ page }}</span>
        <button @click="loadCards(page + 1)" :disabled="cards.length < 20">Suivant</button>
    </div>

    <div class="card-list">
        <p v-if="loading">Chargement...</p>
        <div v-else class="card-result" v-for="card in cards" :key="card.id">
            <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                {{ card.name }} <span>({{ card.uuid }})</span>
            </router-link>
        </div>
    </div>
</template>

<style scoped>
.pagination {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    align-items: center;
}
.card-result {
    margin-bottom: 5px;
}
</style>
