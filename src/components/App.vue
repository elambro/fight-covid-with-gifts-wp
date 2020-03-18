
<template>
    <div>
        <CheckoutForm :endpoint="endpoint"></CheckoutForm>
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
    
    import CheckoutForm from './CheckoutForm';

    const DEBUG = false;

    export default {

        name: 'Covid',
        components: {CheckoutForm},
        props: {
            endpoint: {
                type: String,
                required: false
            }
        },

        data() {
            return {

            };
        },
        mounted() {

        },
        methods: {

        },
        beforeDestroy () {

        }
    };
</script>