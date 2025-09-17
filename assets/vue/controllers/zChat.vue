<template>
    <div>Hello {{ nick }}!</div>


<!--    A list with all connected users will be here...-->
    <div>
            <!-- List of users will be dynamically populated here (ul/li foreach vue) -->
            <h1>Users:</h1>
            <ul id="users">
                <li v-for="user in users" :key="user.id">{{ user.name }}</li>
            </ul>
    </div>

    <Sse :sseUrl="sseUrlPublic" :debug="true" @disconnected="handlePublicDisconnected" @connected="handlePublicConnected" />
    <Sse :sseUrl="sseUrlPrivate" :debug="false" :private="true" :jwt="sseUrlPrivateJwt" />
    <Sse :sseUrl="sseUrlRooms" :debug="false" />
</template>

<script setup>
import Sse from "./Sse.vue";
import {onMounted, ref} from "vue";

const props = defineProps({
    nick: String,
    sseUrlPublic: String,
    sseUrlPrivate: String,
    sseUrlPrivateJwt: String,
    sseUrlRooms: String
});

const users = ref([]) ;

const handlePublicConnected = async(user) =>  {
    users.value.push(user);
}

const handlePublicDisconnected = async(user) => {
    const index = users.value.findIndex(u => u.name === user.name);
    if (index !== -1) {
        users.value.splice(index, 1);
    }

}
const fetchUsers = async() => {
    fetch('/chat/users')
        .then(response => response.json())
        .then(data => {
            users.value = data;
        });
}

const connectToChat = async() => {
    fetch('/chat/connect/' + props.nick)
        .then(response => response.json())
        .then(data => {
            console.log('Connected !');
            const me = {'id' : 0 , 'name' : props.nick}
            handlePublicConnected(me);
        });
}

onMounted(async () => {
    await connectToChat();
    setTimeout(fetchUsers,1000);

});
</script>
