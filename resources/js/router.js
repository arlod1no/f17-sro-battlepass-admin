import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: "/",
        redirect: "/admin/seasons"
    },
    {
        path: "/admin",
        component: () => import("./Admin/AdminLayout.vue"),
        children: [
            {
                path: "",
                redirect: "seasons"
            },
            {
                path: "seasons",
                component: () => import("./Admin/Seasons.vue"),
            },
            {
                path: "battlepasses",
                component: () => import("./Admin/Battlepasses.vue"),
            },
            {
                path: "quests",
                component: () => import("./Admin/Quests.vue"),
            },
            {
                path: "rewards",
                component: () => import("./Admin/Rewards.vue"),
            },
            {
                path: "user-battlepasses",
                component: () => import("./Admin/UserBattlepasses.vue"),
            },
        ],
    },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
