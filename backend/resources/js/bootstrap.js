import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '68dd12ef9865c7217580',
    cluster: 'mt1',
    encrypted: true,
    auth: {
        headers: {
            Authorization: 'Bearer ' + yourTokenLogin
        },
    },
});