<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {defineProps, computed, ref, reactive, watch} from 'vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Checkbox from "@/Components/Checkbox.vue";
import DangerButton from "@/Components/DangerButton.vue";

const player = ref(null);
const showCaptureAudio = ref(true);

const state = reactive({
    stream: null,
    streamActive: computed(() => state.stream?.active),
    isRecording: computed(() => state.recorder?.state === "recording"),
    audioStream: null,
    recorder: null,
    videoChunks: [],
    videoBlob: null,
    videoUrl: computed(() => state.videoBlob ? URL.createObjectURL(state.videoBlob) : null),
    video: null,
});

const startRecording = () => {

    const stream = new MediaStream([
        ...state.stream.getTracks(),
        ...(state.audioStream ? state.audioStream.getTracks() : []),
    ]);

    state.recorder = new MediaRecorder(stream)

    state.recorder.start(3000)

    state.recorder.addEventListener("dataavailable", event => {
        if(!event.data || event.data.size <= 0) return;

        state.videoChunks.push(event.data);

    });

    state.recorder.onstop = () => {

        console.log(state.videoChunks);

        state.videoBlob = new Blob(state.videoChunks, {
            type: "video/mp4"
        });

        state.videoChunks = [];

    }

}

const stopRecording = () => {


    state.recorder.stream.getTracks().forEach(track => track.stop());

    state.stream = null;
    state.recorder = null;

    // console.log(state.video);
    // state.videoBlob = new Blob(state.videoChunks, {
    //     type: "video/mp4"
    // });
    //
    // state.videoUrl = URL.createObjectURL(state.videoBlob);
    //
    // state.video = document.createElement("video");
    // state.video.src = state.videoUrl;
    // document.body.appendChild(state.video);
    // state.video.controls = true;
    // state.video.play();


    // setTimeout(() => {
    //     mediaRecorder.stop();
    // }, 3000);
}

const captureWebcam = () => {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {

            state.stream = stream
            // player.srcObject = stream;

            // console.log(stream);

        });
}

const captureScreen = () => {
    navigator.mediaDevices.getDisplayMedia({ video: true, audio: false })
        .then(stream => {

            state.stream = stream

            // state.stream = stream
            // // player.srcObject = stream;
            //
            // console.log(stream);
            //
            // const mediaRecorder = new MediaRecorder(stream);
            // mediaRecorder.start();
            //
            // const videoChunks = [];
            // mediaRecorder.addEventListener("dataavailable", event => {
            //     videoChunks.push(event.data);
            // });
            //
            // mediaRecorder.addEventListener("stop", () => {
            //     const videoBlob = new Blob(videoChunks, {
            //         type: "video/mp4"
            //     });
            //     const videoUrl = URL.createObjectURL(videoBlob);
            //     const video = document.createElement("video");
            //     video.src = videoUrl;
            //     document.body.appendChild(video);
            //     video.controls = true;
            //     video.play();
            // });
            //
            // setTimeout(() => {
            //     mediaRecorder.stop();
            // }, 3000);
        });
}

const captureAudio = () => {
    navigator.mediaDevices.getUserMedia({ audio: {
            echoCancellation: true,
            noiseSuppression: true,
            autoGainControl: true,
            }, video: false })
        .then(stream => {

            state.audioStream = stream
        });
}

watch(() => state.stream, (stream) => {
    if (stream) {
        player.value.srcObject = stream;

        if(showCaptureAudio.value) {
            captureAudio();
        }
    }
});

</script>

<template>
    <Head title="Video Capture" />

    <AuthenticatedLayout>

        <div class="py-12">

            {{ state }}

            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div v-show="state.videoUrl" class="flex justify-center">
                            <video class="w-full" :src="state.videoUrl" controls></video>
                        </div>

                        <div v-show="!state.videoUrl">

                            <div v-show="state.streamActive" class="space-y-6">
                                <video class="w-full" ref="player" autoplay></video>

                                <div class="flex justify-center">

                                    <PrimaryButton class="justify-center mt-2" @click="startRecording" v-if="!state.isRecording">
                                        Start Recording
                                    </PrimaryButton>

                                    <DangerButton class="justify-center mt-2" @click="stopRecording" v-if="state.isRecording">
                                        Stop Recording
                                    </DangerButton>

                                </div>

                            </div>

                            <div v-show="!state.streamActive" class="flex items-center justify-center space-x-4">

                                <PrimaryButton class="" @click="captureWebcam" v-if="!state.isRecording">Capture Webcam</PrimaryButton>

                                <PrimaryButton class="" @click="captureScreen" v-if="!state.isRecording">Capture Screen</PrimaryButton>

                                <Checkbox id="audio" v-model:checked="showCaptureAudio"></Checkbox>
                                <label for="audio">Enable Audio</label>

                            </div>

                        </div>


                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
