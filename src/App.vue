
<template>
    <div>
        Buy a gift certificate
    </div>

<!--         <div v-if="savedId">
            <h1 style="color: #33a6d7;">Thank you for your submission. We will contact the winners by email!</h1>
            <p>Do you want to <a href="javascript:void(0)" @click="clear">restart?</a></p>
        </div>

        <form v-else ref="form" method="post" :action="endpointSave" @submit.prevent="submit">

            <div class="form-row mb-3">
                <label class="col-md-3">Name</label>
                <div class="col-md-9">
                    <input type="text" name="u_name" v-model="userName" class="form-control" placeholder="e.g. Jane Smith" />
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">Email <span class="required">*</span></label>
                <div class="col-md-9">
                    <input type="email" name="u_email" v-model="userEmail" required class="form-control" placeholder="your@email.com" />
                    <small class="form-text text-muted">We will contact you by email if you win.</small>
                </div>
            </div>

            <div class="alert alert-danger" v-if="errorMsg" role="alert">
              {{ errorMsg }}
            </div>

            <div class="d-sm-flex mt-5">
                <p class="flex-shrink-0">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" :disabled="saving || uploading">
                        <span v-if="uploading">...Uploading</span>
                        <span v-else-if="saving">...Saving</span>
                        <span v-else>Send My Story</span>
                    </button>
                </p>
            </div>

            <input type="hidden" :name="nonceField" :value="nonceData" />

        </form>

    </div> -->
</template>

<script>

    import axios      from 'axios';

    const DEBUG = false;

    export default {

        name: 'Covid',

        props: {

        },

        wp: typeof ajax_object !== 'undefined' ? ajax_object : {},

        data() {
            return {
                userName   : '',
                hash   : null,
                userEmail  : '',
                userZip    : '',
                userStory  : '',
                userPhotos : [],
                width      : null,
                debounce   : null,
                savedPhotos: [],
                uploading  : false,
                saving     : false,
                savedId    : null,
                savedEmail: '',
                errorMsg : null,
                timeout : null,
            };
        },

        computed: {
            nonceData() {
                return this.$options.wp.nonce_data;
            },
            nonceField() {
                return this.$options.wp.nonce_field;
            },
            nonce() {
                return {[this.nonceField] : this.nonceData };
            },
            endpointSave() {
                return this.$options.wp.endpoint_save
            }
        },
        mounted() {

        },
        methods: {
            async send() {

                this.saving = true;

                DEBUG && console.log('-----  Posting form...');
                axios.post( this.endpointSave ,this.getFormData())
                .then( response => this.storeSavedDataFromBody(response.data) )
                .then( attributes => {
                    if (!this.hash) {
                        throw new Error('Missing hashed id!');
                    }
                    this.saving    = false;
                    this.uploading = true;
                    return this.uploadFiles();
                })                
                .then( uppyResult => {
                    if (uppyResult.failed.length) {
                        return false;
                    }
                    DEBUG && console.log('------ Remaining uploads? ', this.allUploaded);
                    DEBUG && console.log('------ End response:', uppyResult); // [successful, failed]
                    // let msg = (((response||{}).data||{}).data||{}).message || 'Your submission was saved.';
                    return true;
                })
                .then( completed => {
                    if (completed) {
                        this.savedId = this.hash;
                    }
                })
                .catch( error => {
                    DEBUG && console.error('------ Error in saving form...', error, error.response, error.response.data);
                    let res = ((((error||{}).response)||{}).data||{}).data;
                    let msg = res || error.message || 'Sorry, something went wrong';
                    this.showError(msg);
                })
                .finally( () => {
                    this.saving    = false;
                    this.uploading = false;
                });
            },
            clear()
            {
                this.hash        = null;
                this.userStory   = null;
                this.userPhotos  = [];
                this.savedPhotos = [];
                if (this.$refs.uploader) {
                    this.$refs.uploader.reset();
                }
                this.savedId    = null;
                this.savedEmail = null;
            }
        },
        beforeDestroy () {
            window.removeEventListener('resize', this.debounce);
        }
    };
</script>
<style>
    textarea.form-control {
        color: #232323;
        border: 1px solid #777;
    }
    label .required { color: red; }
    label.section-label { font-weight: bold; }
</style>