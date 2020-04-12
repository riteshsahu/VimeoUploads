<template>
  <div>
    <button id="uppy-select-files" class="btn btn-primary">Upload Video</button>
    <div
      class="alert mt-3"
      :class="'alert-' + status.type"
      role="alert"
      v-show="status.description"
      v-text="status.description"
    ></div>

    <form>
      <div class="form-group">
        <div ref="dashboardContainer"></div>
      </div>
    </form>
  </div>
</template>

<script>
import Uppy from "@uppy/core";
import XHRUpload from "@uppy/xhr-upload";
import Dashboard from "@uppy/dashboard";
import Form from "@uppy/form";

// import notify from './mixins/noty';

import "@uppy/core/dist/style.css";
import "@uppy/dashboard/dist/style.css";

export default {
  // props: {
  //   maxFileSizeInBytes: {
  //     type: Number,
  //     required: true
  //   }
  // },
  // mixins: [notify],
  data() {
    return {
      payload: null,
      previewPath: null,
      disabled: true,
      status: {
        type: "",
        description: ""
      }
    };
  },
  mounted() {
    this.instantiateUppy();
  },
  methods: {
    instantiateUppy() {
      this.uppy = Uppy({
        debug: true,
        restrictions: {
          // maxFileSize: this.maxFileSizeInBytes,
          minNumberOfFiles: 1,
          maxNumberOfFiles: 1,
          allowedFileTypes: ["video/*"]
        }
      })
        .use(Dashboard, {
          hideUploadButton: false,
          height: 450,
          target: this.$refs.dashboardContainer,
          replaceTargetContent: true,
          showProgressDetails: true,
          browserBackButtonClose: true
        })
        .use(XHRUpload, {
          limit: 10,
          endpoint: "/video/upload",
          formData: true,
          fieldName: "file",
          headers: {
            "X-CSRF-TOKEN": document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute("content") // from <meta name="csrf-token" content="{{ csrf_token() }}">
          }
        });

      this.uppy.on("complete", event => {
        if (event.successful[0] !== undefined) {
          this.payload = event.successful[0].response.body.path;
          this.confirmUpload();
        }
      });

      uppy.on("dashboard:modal-open", () => {
        this.setStatus("", "");
      });
    },

    setStatus(type, description) {
      this.status = { type, description };
      return this;
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
    },
    confirmUpload() {
      if (this.payload) {
        axios
          .post("/store", { file: this.payload })
          .then(({ data }) => {
            this.updatePreviewPath(data)
              .resetUploader()
              .setStatus("success", "Upload Successful!")
              .closeModal();
          })
          .catch(err => {
            console.error(err);

            this.resetUploader();
          });
      }
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
