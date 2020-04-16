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
                :disabled="isUploadInProgress"
                id="uppy-select-files"
                class="btn btn-primary"
                :class="!isUploadInProgress ? 'btn-primary' : 'btn-secondary'"
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
            isUploadInProgress: false
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
                

            this.uppy.on("upload", async data => {
                // data object consists of `id` with upload ID and `fileIDs` array
                // with file IDs in current upload
                // data: { id, fileIDs }
                console.log(
                    `Starting upload id: ${data.id}, fileIDs: ${data.fileIDs}`
                );
                // await this.getUploadLink();
                console.log("upload link", this.uploadLink);
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
            });

            this.uppy.on("upload-success", (file, response) => {
                console.log("upload-success", file.name, response.uploadURL);
                this.selectedFile = null;
                this.isUploadInProgress = false;
            });

            this.uppy.on("upload-error", (file, error, response) => {
                console.log("error with file:", file.id);
                this.isUploadInProgress = false;
                console.log("error message:", error);
            });

            this.uppy.on("upload-retry", fileID => {
                console.log("upload retried:", fileID);
            });

            this.uppy.on("complete", event => {
                console.log("complted", event);
                this.isUploadInProgress = false;
                this.selectedFile = null;

                if (event.successful[0] !== undefined) {
                    // this.payload = event.successful[0].response.body.path;
                    // this.confirmUpload();
                }
            });

            this.uppy.on("dashboard:modal-open", () => {
                this.setStatus("", "");
            });

            this.uppy.on("file-added", file => {
                console.log("file-added", file);
                this.selectedFile = file;
                console.log("this.selectedFile", this.selectedFile);
            });

            this.uppy.on("file-removed", file => {
                this.selectedFile = null;
                console.log("Removed file", file);
            });
        },

        setStatus(type, description) {
            this.status = { type, description };
            return this;
        },

        startUpload() {
            this.isUploadInProgress = true;
            this.getUploadLink()
            .then(uploadLink => {
                this.uploadLink = uploadLink;
                console.log("startupload", uploadLink, this.uploadLink)
                this.uppy.use(Tus, {
                    uploadUrl: uploadLink,
                    resume: true,
                    autoRetry: true,
                    retryDelays: [0, 1000, 3000, 5000]
                });
                this.uppy.upload();
            });
        },

        getUploadLink() {
            return axios
                .post(
                    "/videos/uploadLink",
                    { ...this.selectedFile },
                    {
                        headers: {
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content")
                        }
                    }
                )
                .then(res => {
                    if (res.data) {
                        const data = res.data.body;
                        console.log("uploadLink Data", data);
                        return data.upload.upload_link;
                    }
                })
                .catch(err => {
                    throw err;
                });
        },

        closeModal() {
            this.uppy.getPlugin("Dashboard").closeModal();
            return this;
        },

        updatePreviewPath({ path }) {
            this.previewPath = path;

            return this;
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
