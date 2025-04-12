<template>
  <div class="home">
    <HeroSection />
    <Feature />
    <Guide />
    <Terms />
    <Contact />
  </div>
</template>

<script>
// @ is an alias to /src
import HeroSection from "@/components/HeroSection.vue";
import Feature from "@/components/Feature.vue";
import Guide from "@/components/Guide.vue";
import Contact from "@/components/Contact.vue";
import Terms from "@/components/Terms.vue";

export default {
  name: "HomeView",
  components: {
    HeroSection,
    Feature,
    Guide,
    Terms,
    Contact,
  },
};
</script>
