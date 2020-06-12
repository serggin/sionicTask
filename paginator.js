Vue.component('paginator', {
  props: ['page', 'pages', 'limit', 'onclick'],
  template: `
<div class="paginator">
    <template v-for="label in labels">
        <a v-if="!isNaN(label.label) && label.active" @click="onclick(label.label)">{{label.label}}</a>
        <span v-if="isNaN(label.label) || !label.active">{{label.label}}</span>
    </template> 
</div>
`,
  computed: {
    labels() {
      var labels = [];
      if (this.page >0 && this.pages > 0 && this.limit > 1) {
        this.page = this.page <= this.pages ? this.page : this.pages;
        var half = Math.floor(this.limit / 2);
        var max = this.page + half;
        max = max > this.pages ? this.pages : max;
        var min = max - this.limit + 1;
        min = min < 1 ? 1 : min;
        max = min + this.limit -1;
        max = max > this.pages ? this.pages : max;
        min = min < 2 ? 2 : min;

        labels.push({
          label: 1,
          active: this.page > 1
        });
        if (min > 2) {
          labels.push ({label: "..."});
        }
        for (var i=min; i<=max;i++) {
          labels.push({
            label: i,
            active: i !== this.page
          });
        }
        if (max < this.pages-1) {
          labels.push ({label: "..."});
        }
        if (max < this.pages) {
          labels.push ({
            label: this.pages,
            active: true
          });
        }
      }
      return labels;
    }
  }
});
