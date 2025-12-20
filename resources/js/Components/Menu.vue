<script>
import { Link } from '@inertiajs/vue3';
import { layoutComputed } from "@/state/helpers";
import simplebar from "simplebar-vue";

export default {
  components: {
    simplebar,
    Link
  },
  data() {
    return {
      settings: {
        minScrollbarLength: 60,
      },
      menuItems: [
        { title: this.$t("t-menu"), type: "title" },
        {
          icon: "ri-dashboard-2-fill",
          title: this.$t("t-dashboards"),
          type: "link",
          target: "#/"
        },
        {
          icon: "ri-stethoscope-line",
          title: this.$t("t-doctor"),
          type: "link",
          target: "#medicos",
        },
        {
          icon: "ri-user-heart-fill",
          title: this.$t("t-patient"),
          type: "link",
          target: "#pacientes",
          children: [
            { href: "/pacientes", label: this.$t("t-register") },
            { href: "/pacientes/editar", label: this.$t("t-edit") }
          ]
        },
        {
          title: this.$t("t-settings"),
          type: "title",
          icon: "ri-more-fill"
        },
        {
            icon: "ri-user-fill",
            title: this.$t("t-user"),
            type: "link",
            target: "/usuarios",
        },
        {
            icon: "ri-layout-grid-fill",
            title: this.$t("t-components"),
            type: "link",
            target: "/componentes",
        },

        {
          icon: "ri-account-circle-line",
          title: this.$t("t-authentication"),
          type: "link",
          target: "#sidebarAuth",
          children: [
            {
              title: this.$t("t-signin"),
              target: "#sidebarSignIn",
              children: [
                { href: "/auth/signin-basic", label: this.$t("t-basic") },
                { href: "/auth/signin-cover", label: this.$t("t-cover") }
              ]
            },
            {
              title: this.$t("t-signup"),
              target: "#sidebarSignUp",
              children: [
                { href: "/auth/signup-basic", label: this.$t("t-basic") },
                { href: "/auth/signup-cover", label: this.$t("t-cover") }
              ]
            },
            {
              title: this.$t("t-password-reset"),
              target: "#sidebarResetPass",
              children: [
                { href: "/auth/reset-pwd-basic", label: this.$t("t-basic") },
                { href: "/auth/reset-pwd-cover", label: this.$t("t-cover") }
              ]
            },
            {
              title: this.$t("t-password-create"),
              target: "#sidebarcreatepassword",
              children: [
                { href: "/auth/create-pwd-basic", label: this.$t("t-basic") },
                { href: "/auth/create-pwd-cover", label: this.$t("t-cover") }
              ]
            },
            {
              title: this.$t("t-lock-screen"),
              target: "#sidebarLockScreen",
              children: [
                { href: "/auth/lockscreen-basic", label: this.$t("t-basic") },
                { href: "/auth/lockscreen-cover", label: this.$t("t-cover") }
              ]
            }
          ]
        }
      ]
    };
  },
  computed: {
    ...layoutComputed,
    layoutType: {
      get() {
        return this.$store ? this.$store.state.layout.layoutType : {} || {};
      }
    }
  },
  mounted() {
    this.initActiveMenu();
    this.onRoutechange();
    this.bindCollapseEvents();
  },
  methods: {
    bindCollapseEvents() {
      let collapses = document.querySelectorAll(".navbar-nav .collapse");
      collapses.forEach(collapse => {
        collapse.addEventListener("show.bs.collapse", (e) => {
          e.stopPropagation();
          let closestCollapse = collapse.parentElement.closest(".collapse");
          if (closestCollapse) {
            let siblingCollapses = closestCollapse.querySelectorAll(".collapse");
            siblingCollapses.forEach(siblingCollapse => {
              if (siblingCollapse.classList.contains("show")) {
                siblingCollapse.classList.remove("show");
                siblingCollapse.parentElement.firstChild.setAttribute("aria-expanded", "false");
              }
            });
          } else {
            let siblings = Array.from(collapse.parentElement.parentNode.children).filter(
              el => el !== collapse.parentElement
            );
            siblings.forEach(item => {
              if (item.childNodes.length > 2) {
                item.firstElementChild.setAttribute("aria-expanded", "false");
                item.firstElementChild.classList.remove("active");
              }
              let ids = item.querySelectorAll("*[id]");
              ids.forEach(item1 => {
                item1.classList.remove("show");
                item1.parentElement.firstChild.setAttribute("aria-expanded", "false");
                item1.parentElement.firstChild.classList.remove("active");
                if (item1.childNodes.length > 2) {
                  item1.querySelectorAll("ul li a").forEach(subitem => {
                    if (subitem.hasAttribute("aria-expanded")) {
                      subitem.setAttribute("aria-expanded", "false");
                    }
                  });
                }
              });
            });
          }
        });

        collapse.addEventListener("hide.bs.collapse", (e) => {
          e.stopPropagation();
          let childCollapses = collapse.querySelectorAll(".collapse");
          childCollapses.forEach(childCollapse => {
            childCollapse.classList.remove("show");
            childCollapse.parentElement.firstChild.setAttribute("aria-expanded", "false");
          });
        });
      });
    },

    onRoutechange() {
      setTimeout(() => {
        let currentPath = window.location.pathname;
        let activeLink = document.querySelector(`#navbar-nav [href="${currentPath}"]`);
        if (activeLink) {
          let position = activeLink.offsetTop;
          let wrapper = document.querySelector("#scrollbar .simplebar-content-wrapper");
          if (wrapper && position > document.documentElement.clientHeight) {
            wrapper.scrollTop = position + 300;
          }
        }
      }, 500);
    },

    initActiveMenu() {
      setTimeout(() => {
        let currentPath = window.location.pathname;
        let a = document.querySelector(`#navbar-nav [href="${currentPath}"]`);
        if (a) {
          a.classList.add("active");
          let parentCollapseDiv = a.closest(".collapse.menu-dropdown");
          if (parentCollapseDiv) {
            parentCollapseDiv.classList.add("show");
            let trigger = parentCollapseDiv.parentElement.children[0];
            trigger.classList.add("active");
            trigger.setAttribute("aria-expanded", "true");
            let upperCollapse = parentCollapseDiv.parentElement.closest(".collapse.menu-dropdown");
            if (upperCollapse) {
              upperCollapse.classList.add("show");
              upperCollapse.previousElementSibling.classList.add("active");
              let grandparent = upperCollapse.parentElement.closest(".collapse.menu-dropdown");
              if (grandparent) {
                grandparent.classList.add("show");
                grandparent.previousElementSibling.classList.add("active");
              }
            }
          }
        }
      }, 0);
    }
  }
};
</script>

<template>
  <BContainer fluid>
    <div id="two-column-menu"></div>

    <template v-if="layoutType === 'vertical' || layoutType === 'semibox'">
      <ul class="navbar-nav h-100" id="navbar-nav">
        <template v-for="(item, index) in menuItems" :key="index">

          <li v-if="item.type === 'title'" class="menu-title">
            <i v-if="item.icon" :class="item.icon"></i>
            <span>{{ item.title }}</span>
          </li>

          <li v-else-if="item.type === 'link'" class="nav-item">
            <a
              v-if="item.children"
              class="nav-link menu-link"
              :href="item.target"
              data-bs-toggle="collapse"
              role="button"
              aria-expanded="false"
              :aria-controls="item.target.replace('#', '')"
            >
              <i :class="item.icon"></i>
              <span>{{ item.title }}</span>
            </a>

            <Link
              v-else
              class="nav-link menu-link"
              :href="item.target.replace('#', '')"
            >
              <i :class="item.icon"></i>
              <span>{{ item.title }}</span>
            </Link>

            <div v-if="item.children" class="collapse menu-dropdown" :id="item.target.replace('#', '')">
              <ul class="nav nav-sm flex-column">
                <template v-for="(child, cidx) in item.children" :key="cidx">
                  <li v-if="child.href" class="nav-item">
                    <Link :href="child.href" class="nav-link">{{ child.label }}</Link>
                  </li>
                  <li v-else-if="child.children" class="nav-item">
                    <a
                      class="nav-link"
                      :href="child.target"
                      data-bs-toggle="collapse"
                      role="button"
                      aria-expanded="false"
                      :aria-controls="child.target.replace('#', '')"
                    >
                      {{ child.title }}
                    </a>

                    <div class="collapse menu-dropdown" :id="child.target.replace('#', '')">
                      <ul class="nav nav-sm flex-column">
                        <li v-for="(sub, subIdx) in child.children" :key="subIdx" class="nav-item">
                          <Link :href="sub.href" class="nav-link">{{ sub.label }}</Link>
                        </li>
                      </ul>
                    </div>
                  </li>
                </template>
              </ul>
            </div>
          </li>
        </template>
      </ul>
    </template>
  </BContainer>
</template>
