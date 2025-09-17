<template>
    <div v-if="debug">
        SSE DEBUG
         {{ jwt }}
        <p>SSE URL: {{ sseUrl }}</p>
        <fieldset>
            <legend>Data</legend>
            <div v-html="dataDebug"></div>
        </fieldset>
    </div>
</template>

<script setup>
import {ref} from "vue";

const props = defineProps({
    sseUrl: String,
    jwt: String,
    debug: Boolean
});

const emit = defineEmits(['someBodyIsConnected','someBodyIsDisconnected','connected','disconnected']);

let dataDebug = ref('');

if (props.debug)
{
    dataDebug.value = "Connecting to server...<br/><br/>"
}
//const hubUrl = response.headers.get('Link').match(/<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/)[1];
//alert(hubUrl);

let eventSource = null ;

// Todo : add jwt !!!
if (props.jwt)
    eventSource = new EventSource(props.sseUrl + "&authorization=" + props.jwt, { withCredentials: false });
else
    eventSource = new EventSource(props.sseUrl, { withCredentials: true });

eventSource.onopen = () => {
    if (props.debug)
    {
        dataDebug.value += "Connection to server opened.<br/><br/>"
    }
    emit('connected');
};

eventSource.onerror = (error) => {
    if (props.debug)
    {
        dataDebug.value += "Error: " + JSON.stringify(error) + "<br/><br/>"
    }
};

eventSource.onclose = () => {
    if (props.debug)
    {
        dataDebug.value += "Connection to server closed.<br/><br/>"
    }
    emit('disconnected');
};

// type (message default)
eventSource.addEventListener("ping", (event) => {
    console.log(event);
});

eventSource.onmessage = event => {
    console.log(event);
    const data = JSON.parse(event.data);

    if (props.debug)
    {
        dataDebug.value += event.data + "<br/><br/>"
    }

    if (data.action === 'connected')
    {
        console.log('CONNECT : ' + data.nick);
        emit('someBodyIsConnected', { id: '000' ,  name : data.nick });

    }

    if (data.action === 'disconnected')
    {
        console.log('DISCONNECT : ' + data.nick);
        emit('someBodyIsDisconnected', { id: '000' ,  name : data.nick });

    }


    console.log(data)

}
</script>
