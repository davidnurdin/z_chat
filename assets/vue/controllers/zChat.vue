<template>
    <div>Hello {{ nick }}!</div>

    <Sse :sseUrl="sseUrlPublic" :debug="false" />
    <Sse :sseUrl="sseUrlPrivate" :debug="false" :private="true" :jwt="sseUrlPrivateJwt" />
    <Sse :sseUrl="sseUrlRooms" :debug="true" />
</template>

<script setup>
import Sse from "./Sse.vue";

defineProps({
    nick: String,
    sseUrlPublic: String,
    sseUrlPrivate: String,
    sseUrlPrivateJwt: String,
    sseUrlRooms: String
});


</script>
