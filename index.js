Vue.component('sionic-table', {
  props: ['page', 'limit', 'total', 'cities', 'tableData', 'getQuantity', 'getPrice'],
  template: `
<div>
  <h3>Sionic table</h3>
  <div>page = {{page}}</div>
  <div>limit = {{limit}}</div>
  <div>total = {{total}}</div>
  <table>
    <thead>
      <tr>
        <th rowspan="2">id</th>
        <th rowspan="2">name</th>
        <th rowspan="2">code</th>
        <th rowspan="2">weight</th>
        <th rowspan="2">usage</th>
        <template v-for="item in cities">
          <th colspan="2" :key="item.abbr">{{item.city}}</th>
        </template>
      </tr>
      <tr>
        <template v-for="item in cities">
          <th>quantity</th>
          <th>price</th>
        </template>      
      </tr>
    </thead>
    <tbody>
      <tr v-for="rowData in tableData" :key="rowData.code">
        <td>{{ rowData.id }}</td>
        <td>{{ rowData.name }}</td>
        <td>{{ rowData.code }}</td>
        <td>{{ rowData.weight }}</td>
        <td>{{ rowData.usage }}</td>
        <template v-for="city in cities">
          <td> {{ getQuantity(rowData, city.abbr) }}</td>
          <td> {{ getPrice(rowData, city.abbr) }}</td>
        </template>
     </tr>
    </tbody>
  </table>
</div>
  `
});

var app= new Vue({
  el: '#app',
  data: {
    error: false,
    total: 0,
    page: 1,
    offset: 0,
    limit: 10,
    //cities: [{city:"Москва", abbr: "msk"}],
    cities: [],
    tableData: []
  },
  methods: {
    getQuantity(rowData, abbr) {
      var field = "quantity_" + abbr;
      return rowData[field];
    },
    getPrice(rowData, abbr) {
      var field = "price_" + abbr;
      return rowData[field];
    },
    fetchData(offset, limit) {
      fetch(`backend.php?limit=${limit}&offset=${offset}`)
        .then(response => {
          if (response.ok) {
            return response.json();
          } else {
            return response.text().then(error => {throw error});
          }
        })
        .then(json => {
          //this.cities = JSON.parse(json);
          this.tableData = json;
          console.log("tableData = ", json);
        })
        .catch(error => {
          console.error(error);
          this.error = error;
        });
    }
  },
  created() {
    fetch('backend.php?total')
      .then(response => {
        if (response.ok) {
          return response.text();
        } else {
          return response.text().then(error => {throw error});
        }
      })
      .then(text => {
        //console.log("response.text = ", text);
        this.total = parseInt(text);
      })
      .catch(error => {
        console.error(error);
        this.error = error;
      });


    fetch('backend.php?cities')
      .then(response => {
        if (response.ok) {
          return response.json();
        } else {
          return response.text().then(error => {throw error});
        }
      })
      .then(json => {
        //this.cities = JSON.parse(json);
        this.cities = json;
        console.log("cities = ", json);
      })
      .catch(error => {
        console.error(error);
        this.error = error;
      });

    this.fetchData(this.offset, this.limit);

  }
});