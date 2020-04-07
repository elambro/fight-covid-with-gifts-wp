import axios from 'axios';

const api = () => axios;

var csrf_field;
var csrf_value;

if (typeof ajax_object !== 'undefined') {
    // This is WordPress.
    var csrf_field = ajax_object.csrf_field;
    var csrf_value = ajax_object.csrf_data;
}

export default {

    async get(endpoint, params={}, options={})
    {
        options.params = params;
        return api().get(endpoint, options)
            .then( response => this.formatResponse(response))
            .catch( err => Promise.reject(this.handleError(err)))
    },

    async post(endpoint, data, options={})
    {
        data = this.formatData(data);
        return api().post(endpoint, data, options)
            .then( response => this.formatResponse(response))
            .catch( err => Promise.reject(this.handleError(err)))
    },

    formatData(data)
    {
        let form = new FormData;
        data && Object.keys(data).forEach(key => {
            let val = data[key];

            if (val === 'null') {
                val = null;
            } else if (val === 'false' || val === false) {
                val = 0;
            } else if (val === 'true' || val === true) {
                val = 1;
            }
            
            form.append(key, val);
        })
        if (csrf_field) {
            form.append(csrf_field, csrf_value);
        } else {
            this.warn('No nonce was found!', ajax_object);
        }
        return form;
    },

    formatResponse(response)
    {
        // this.log('Formatting response:', response);
        if (!((response||{}).data||{}).data) {
            throw {trans:'errors.connection', other:[]};
        }
        return response.data.data;
    },

    handleError(err)
    {
        if ((err||{}).response) {

            if (typeof err.response.data === 'string') {
                return err.response.data;
            }

            let data = this.formatResponse(err.response);
            this.warn('Error response data:', data);
            if (data.trans) {
                return data;
            }

            let status = err.response.status;
            if (status === 422) {
                return {trans:'errors.invalid', other:[]};
            }
            return {trans:'errors.connection', other:[]};
        }

        this.warn('Error:', err);
        return {trans:'errors.whoops', other:[]};
    },

    warn(str,dump)
    {
        (console||{}).warn && console.warn(str, dump);
    },
    log(str,dump)
    {
        (console||{}).log && console.log(str, dump);
    }
}
