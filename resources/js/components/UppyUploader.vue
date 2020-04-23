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
            isUploadButtonDisabled: true,
            createdVideoData: null
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
                    resume: true,
                    autoRetry: true,
                    retryDelays: [0, 1000, 3000, 5000],
                    metaFields: null,
                    limit: 1,
                    headers: {
                        Accept: "application/vnd.vimeo.*+json;version=3.4"
                    },
                    chunkSize: 4194304          // chunk size set to 4 mb
                });

            this.uppy.on("upload-success", (file, response) => {
                this.selectedFile = null;
                this.isUploadButtonDisabled = false;
                this.updateVideoStatusToSuccess();
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
            this.createVideo().then(data => {
                this.createdVideoData = data.data.data;
                this.uppy.getPlugin("Tus").setOptions({
                    uploadUrl: data.headers.location
                });
                this.uppy.upload();
            });
        },

        updateVideoStatusToSuccess() {
            return axios
                .patch(`/me/videos/${this.createdVideoData.id}`, {
                    upload_success: true
                })
                .then(() => {
                    console.log("Video uploaded succesfully");
                });
        },

        createVideo() {
            return axios
                .post(`/me/videos/`, {
                    filename: this.selectedFile.name,
                    size: this.selectedFile.size
                })
                .then(data => {
                    return data;
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
