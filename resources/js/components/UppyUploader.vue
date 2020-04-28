<template>
    <div>
        <div class="form-group">
            <div ref="dashboardContainer"></div>
        </div>

        <div class="text-right">
            <button
                @click="startUpload()"
                :disabled="isUploadButtonDisabled"
                id="uppy-select-files"
                class="btn"
                :class="
                    isUploadButtonDisabled ? 'btn-secondary' : 'btn-primary'
                "
            >
                Upload Video
            </button>
        </div>
    </div>
</template>

<script>
import Uppy from "@uppy/core";
import Dashboard from "@uppy/dashboard";
import "@uppy/core/dist/style.css";
import "@uppy/dashboard/dist/style.css";
import Tus from "@uppy/tus";

export default {
    data() {
        return {
            selectedFile: null,
            isUploadButtonDisabled: true
        };
    },
    mounted() {
        this.instantiateUppy();
    },
    methods: {
        instantiateUppy() {
            this.uppy = Uppy({
                logger: Uppy.debugLogger,
                restrictions: {
                    minNumberOfFiles: 1,
                    maxNumberOfFiles: 1,
                    allowedFileTypes: ["video/*"]
                },
                allowMultipleUploads: false
            })
                .use(Dashboard, {
                    hideUploadButton: false,
                    height: 300,
                    inline: true,
                    target: this.$refs.dashboardContainer,
                    replaceTargetContent: true,
                    showProgressDetails: true,
                    browserBackButtonClose: true,
                    closeModalOnClickOutside: true,
                    hideUploadButton: true
                })
                .use(Tus, {
                    endpoint: "/me/videos",
                    // endpoint: "https://master.tus.io/files/",
                    resume: true,
                    autoRetry: true,
                    retryDelays: [0, 1000, 3000, 5000],
                    metaFields: null,
                    limit: 1
                    // chunkSize: 4194304 // set chunk size to 4 mb
                });

            this.uppy.on("upload-success", (file, response) => {
                console.log("upload sucessd");
                this.isUploadButtonDisabled = false;
                this.updateVideoStatusToSuccess(btoa(this.selectedFile.id));
                this.selectedFile = null;
            });

            this.uppy.on("upload-error", (file, error, response) => {
                this.isUploadButtonDisabled = false;
            });

            this.uppy.on("complete", event => {
                this.isUploadButtonDisabled = false;
                this.selectedFile = null;
            });

            this.uppy.on("file-added", file => {
                this.selectedFile = file;

                this.uppy.setMeta({
                    fileId: file.id
                });

                this.isUploadButtonDisabled = false;
            });

            this.uppy.on("file-removed", file => {
                this.selectedFile = null;
                this.isUploadButtonDisabled = true;
            });
        },

        startUpload() {
            this.isUploadButtonDisabled = true;
            this.uppy.upload();
        },

        updateVideoStatusToSuccess(id) {
            axios
                .patch(`/me/videos/${id}`, {
                    upload_success: true
                })
                .then(() => {
                    console.log("Video uploaded succesfully");
                });
        }
    }
};
</script>

<style scoped>
.image-container {
    height: 150px;
    width: 150px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: auto;
    margin-left: auto;
}

.image-container > img {
    width: inherit;
    height: inherit;
}
</style>
