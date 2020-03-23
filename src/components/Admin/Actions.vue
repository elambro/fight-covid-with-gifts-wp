
<template>
    <span v-if="saving">{{ $t('form.buttons.saving') }}</span>
    <span v-else-if="deleted" style="color: red;">Deleted</span>
    <span v-else>


        <span v-if="isUsed">
            <a href="javascript:void(0)" @click="markUnused">Mark Unused</a>
            <span v-if="isPaid"> 
            | <a href="javascript:void(0)" @click="markUnpaid">Mark Unpaid</a>
            </span>
            | <a href="javascript:void(0)" @click="markCancelled">Cancel</a>
        </span>

        <span v-else-if="isAccepted">
            <a href="javascript:void(0)" @click="markUsed">Mark Used</a>
            <span v-if="isPaid">
            | <a href="javascript:void(0)" @click="markUnpaid">Mark Unpaid</a>
            </span>
            | <a href="javascript:void(0)" @click="markCancelled">Cancel</a>
            </span>

        <span v-else-if="isPaid && !isCancelled">
            <a href="javascript:void(0)" @click="markAccepted">Mark Accepted</a>
            | <a href="javascript:void(0)" @click="markUnpaid">Mark Unpaid</a>
            | <a href="javascript:void(0)" @click="markCancelled">Cancel</a>
        </span>

        <span v-else-if="!isPaid && !isCancelled">
            <a href="javascript:void(0)" @click="markPaid">Mark Paid</a>
            | <a href="javascript:void(0)" @click="markCancelled">Cancel</a>
        </span>

        <span v-else-if="isCancelled">
            <a v-if="isPaid" href="javascript:void(0)" @click="markUnpaid">Mark Unpaid</a>
            <a v-else href="javascript:void(0)" @click="markPaid">Mark Paid</a>
            | <a href="javascript:void(0)" @click="deleteItem" style="color:red">Delete</a>
        </span>

    </span>
</template>

<script>
    
    const CONFIG = typeof ajax_object !== 'undefined' ? ajax_object : {};

    export default {

        name: 'AdminActions',

        props: {
            id: {
                type: [Number,String],
                required: true
            },
            attributes: {
                type: Object,
                required: true
            },
            endpoints: {
                type: Object,
                default: () => ({
                    cancel  : CONFIG.cvdapp_cancel,
                    accept  : CONFIG.cvdapp_accept,
                    use     : CONFIG.cvdapp_use,
                    unuse   : CONFIG.cvdapp_unuse,
                    paid    : CONFIG.cvdapp_paid,
                    unpaid  : CONFIG.cvdapp_unpaid,
                    'delete': CONFIG.cvdapp_delete,
                })
            }
        },

        data() {
            return {
                saving: false,
                deleted: false
            }
        },

        computed: {
            isAccepted()
            {
                return (this.attributes||{}).accepted_at ? true : false;
            },
            isCancelled()
            {
                return (this.attributes||{}).cancelled_at ? true : false;
            },
            isPaid()
            {
                return (this.attributes||{}).paid_at ? true : false;
            },
            isUsed()
            {
                return (this.attributes||{}).used_at ? true : false;
            }
        },

        mounted() {
            // this.fetch()
        },

        methods: {
            deleteItem()
            {
                this.send('delete')
            },
            markCancelled()
            {
                this.send('cancel')
            },
            markAccepted()
            {
                this.send('accept')
            },
            markUnpaid()
            {
                this.send('unpaid')
            },
            markPaid()
            {
                this.send('paid')
            },
            markUnused()
            {
                this.send('unuse')
            },
            markUsed()
            {
                this.send('use')
            },
            send(action)
            {
                this.saving = true;
                this.$api.post(this.endpoints[action],{id: this.id})
                .then(data => {
                    if (action === 'delete') {
                        this.deleted = true;
                        this.$el.parentElement.parentElement.style.display = 'none';
                    }
                    this.$parent.onSave(this, action, data)
                })
                .catch(err => this.$parent.onError(err))
                .finally(() => this.saving = false )
            }
        }
    };
</script>