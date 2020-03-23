
<template>
    <div>

        <Messages ref="msg"></Messages>

        <div style="position:absolute">
            <Spinner v-show="saving"></Spinner>
        </div>

        <slot></slot>

    </div>

</template>

<script>
    
    import Messages       from './../Messages'
    import Spinner        from './../Spinner';

    const CONFIG = typeof ajax_object !== 'undefined' ? ajax_object : {};

    export default {

        name: 'AdminTable',
        components: {Messages, Spinner},

        props: {
            
        },

        data() {
            return {
                saving: false,
            }
        },

        mounted() {
            // this.fetch()
        },

        methods: {
            onSave(child, action, data)
            {
                child.$props.attributes = data.attributes;
                this.$refs.msg.showMessage('success', 'Saved.');
            },
            onError(err)
            {
                let msg = this.$t('errors.whoops');
                if (typeof err === 'string') {
                    msg = this.$t(err);
                } else if ((err||{}).trans) {
                    msg = this.$t(err.trans, err.other || []);
                } else if ( typeof (err||{}).message === 'string') {
                    msg = this.$t(err.message);
                }

                msg && this.$refs.msg.showMessage('warning',msg)
            }
        },
        beforeDestroy () {

        }
    };
</script>