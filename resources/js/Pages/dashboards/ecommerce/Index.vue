<script>
import { Head } from '@inertiajs/vue3';

import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";

export default {
  components: {
    Layout,
    Head,
    PageHeader,
  },
};
</script>

<template>
  <Layout>

    <Head title="Dashboard" />
    <PageHeader
      title="Dashboard"
      pageTitle="Menu"
    />
  </Layout>
</template>
