export default [
    { path: '/', redirect: '/home' },
    
    {
        path: '/home',
        name: 'home',
        component: require('./screens/home/IndexComponent').default,
    }
];
