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
                    resume: true,
                    autoRetry: true,
                    retryDelays: [0, 1000, 3000, 5000],
                    metaFields: null,
                    limit: 1,
                    headers: {
                        Accept: "application/vnd.vimeo.*+json;version=3.4"
                    }, 
                    // chunkSize: 4194304
                });

            this.uppy.on("upload-success", (file, response) => {
                this.selectedFile = null;
                this.isUploadButtonDisabled = false;
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
