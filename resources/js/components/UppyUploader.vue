<template>
    <div>
        <div
            class="alert mt-3"
            :class="'alert-' + status.type"
            role="alert"
            v-show="status.description"
            v-text="status.description"
        ></div>

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
            payload: null,
            previewPath: null,
            disabled: true,
            status: {
                type: "",
                description: ""
            },
            uploadLink: "",
            selectedFile: null,
            uploadOffset: 0,
            checkProgressInterval: 0,
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
                    resume: true,
                    autoRetry: true,
                    retryDelays: [0, 1000, 3000, 5000],
                    metaFields: null,
                    limit: 1,
                    headers: {
                        Accept: "application/vnd.vimeo.*+json;version=3.4"
                    }, 
                    chunkSize: 4194304
                });

            this.uppy.on("upload", async data => {
                // data object consists of `id` with upload ID and `fileIDs` array
                // with file IDs in current upload
                // data: { id, fileIDs }
                console.log(
                    `Starting upload id: ${data.id}, fileIDs: ${data.fileIDs}`
                );
            });

            this.uppy.on("upload-progress", (file, progress) => {
                // file: { id, name, type, ... }
                // progress: { uploader, bytesUploaded, bytesTotal }
                console.log(
                    "upload-progress",
                    file.id,
                    progress.bytesUploaded,
                    progress.bytesTotal
                );
                this.uploadOffset = progress.bytesUploaded;
            });

            this.uppy.on("upload-success", (file, response) => {
                console.log("upload-success", file.name, response.uploadURL);
                this.selectedFile = null;
                this.isUploadButtonDisabled = false;
            });

            this.uppy.on("upload-error", (file, error, response) => {
                console.log("error with file:", file.id);
                this.isUploadButtonDisabled = false;
                console.log("error message:", error);
            });

            this.uppy.on("upload-retry", fileID => {
                console.log("upload retried:", fileID);
            });

            this.uppy.on("complete", event => {
                console.log("complted", event);
                this.isUploadButtonDisabled = false;
                this.selectedFile = null;
            });

            this.uppy.on("dashboard:modal-open", () => {
                this.setStatus("", "");
            });

            this.uppy.on("file-added", file => {
                console.log("file-added", file);
                this.selectedFile = file;
                this.isUploadButtonDisabled = false;
                console.log("this.selectedFile", this.selectedFile);
            });

            this.uppy.on("file-removed", file => {
                this.selectedFile = null;
                this.isUploadButtonDisabled = true;
                console.log("Removed file", file);
            });
        },

        setStatus(type, description) {
            this.status = { type, description };
            return this;
        },

        startUpload() {
            this.isUploadButtonDisabled = true;
            this.uppy.upload();
        },

        resetUploader() {
            this.uppy.reset();
            return this;
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
