
new Vue({
  el: '#area',
  data() {
    return {
      val: 'area-input-select',
      isActiveManual: false,
      isActiveSelect: true
    }
  },
  methods: {
    toggleForm: function (e) {
        this.val = e.target.value
        if(this.val == "area-input-manual")
        {
          this.isActiveManual = true
          this.isActiveSelect = false
        }
        if(this.val == "area-input-select")
        {
          this.isActiveManual = false
          this.isActiveSelect = true

          $(function() {
            let initPrefVal = '都道府県を選択'; //selectedを付与したい都道府県
            let initCityVal = '市区町村を選択'; //selectedを付与したい市区町村
            let valType = 'name';  // 'name' or 'code'
    
            // 都道府県リストを作成
            getPrefectureSelection('#area_select_prefecture', '#area_select_city', '/json/prefectureCity.json', valType, initPrefVal, initCityVal);
    
            // 都道府県選択時に市区町村リストを作成
            $('#area_select_prefecture').on('change', function() {
              getCitySelection('#area_select_prefecture', '#area_select_city', '/json/prefectureCity.json', valType);
            });
          });
        }
    }
  }
})


var precipitation_list = []
var n_precipitation_list = 6290
var precipitation_interval = 10
for(var i=0;i<=n_precipitation_list;i++){
  precipitation_list.push(i * precipitation_interval)
}
new Vue({
  el: '#precipitation',
  data: {
      options: precipitation_list
  }
});

var wind_speed_list = []
var n_wind_speed_list = 80
var wind_speed_interval = 10
for(var i=0;i<=n_wind_speed_list;i++){
  wind_speed_list.push(i * wind_speed_interval)
}
new Vue({
  el: '#wind_speed',
  data: {
      options: wind_speed_list
  }
});

var wind_direction_list = ["指定なし","北","北北東","北東", "東北東", "東", "東南東", "南東", "南南東", "南", "南南西", "南西", "西南西", "西", "西北西", "北西", "北北西", "北"];
new Vue({
  el: '#wind_direction',
  data: {
      options: wind_direction_list
  }
});

// google maps api

let map;
let mainMarker;
let marker =[];
let infoWindow = [];

// function initMap() {
//   var opts = {
//     zoom: 10,
//     center: new google.maps.LatLng(35.709984,139.810703)
//   };
//   map = new google.maps.Map(document.getElementById("map"), opts);
// }



// 検索（非同期通信）
function getData() {

  // メッセージ初期化
  document.getElementById("loading").textContent = "検索中です..."
  document.getElementById("liden-result-msg").textContent = ""
  document.getElementById("amedas-result-msg").textContent = ""

  // マップ初期化
  document.getElementById("map").style.width = "0"
  document.getElementById("map").style.height = "0"
  if(document.getElementById("map").childElementCount > 0)
  {
    document.getElementById("map").innerHTML = ""
  }

  // テーブル初期化
  if(document.getElementById("climate-table").childElementCount > 0)
  {
    document.getElementById("climate-table").removeChild(document.getElementById("table"))
  }
  
  if(document.getElementById("area_select_prefecture"))
  {
    var area = document.getElementById("area_select_prefecture").value + document.getElementById("area_select_city").value
  }
  else if(document.getElementById("area_manual"))
  {
    var area = document.getElementById("area_manual").value
  }
  var data = {
    "area": area,
    "start_date": document.getElementById("start_date").value,
    "end_date": document.getElementById("end_date").value,
    "thander": document.getElementById("thander_select").value,
    "precipitation": document.getElementById("precipitation_select").value,
    "wind_speed": document.getElementById("wind_speed_select").value,
    "wind_direction": document.getElementById("wind_direction_select").value
  }
  var json = JSON.stringify(data)
  var xhr = new XMLHttpRequest()
  xhr.open("POST", location.href+"/api")
  xhr.setRequestHeader("content-type", "application/x-www-form-urlencoded;charset=UTF-8")
  xhr.send(json);

  xhr.onreadystatechange = function () {
    try 
    {
      if (xhr.readyState == 4) 
      {
        if (xhr.status == 200) 
        {
          console.log("state 4 and 200 ok.")
          var result = JSON.parse(xhr.response);
          document.getElementById("loading").textContent = ""
          document.getElementById("btn-create-img").style.display = "block"

          // 地図出力
          if(result.liden_data_array != "no_thander")
          {
            document.getElementById("liden-result-msg").textContent = "指定期間内に発生した落雷："+result.liden_data_array.length+"件"
            document.getElementById("map").style.width = "500px"
            document.getElementById("map").style.height = "500px"
            var latlng = new google.maps.LatLng(result.center_lat[0], result.center_lon[0]);
            var opts = {
              zoom: 10,
              center: latlng
            };
            var map = new google.maps.Map(document.getElementById("map"), opts);
  
            for (var i = 0; i < result.liden_data_array.length; i++) 
            {
              const id = result.liden_data_array[i].id;
              // 緯度経度のデータを作成
              let markerLatLng = new google.maps.LatLng({lat: parseFloat(result.liden_data_array[i].lat), lng: parseFloat(result.liden_data_array[i].lon) });
              // マーカーの追加
              marker[i] = new google.maps.Marker({
                position: markerLatLng,
                map: map,
              });
          
              // 吹き出しの追加
              infoWindow[i] = new google.maps.InfoWindow({
                content: `<a href=/laundries/${id}>test</a>`
              });
          
              markerEvent(i);
              function markerEvent(i) {
                marker[i].addListener('click', function() {
                  infoWindow[i].open(map, marker[i]);
                });
              }
            }
          }

          // テーブル出力
          document.getElementById("amedas-result-msg").textContent = "検索条件に一致するアメダスデータ："+result.amedas_data_array.length+"件"
          let table = document.createElement('table');
          table.id = "table"
          let thead = document.createElement('thead');
          let tbody = document.createElement('tbody');
          table.appendChild(thead);
          table.appendChild(tbody);
          document.getElementById('climate-table').appendChild(table);
          let row_1 = document.createElement('tr');
          let heading_1 = document.createElement('th');
          heading_1.innerHTML = "日付";
          let heading_2 = document.createElement('th');
          heading_2.innerHTML = "降水量(mm)";
          let heading_3 = document.createElement('th');
          heading_3.innerHTML = "最大瞬間風速(m/s)";
          let heading_4 = document.createElement('th');
          heading_4.innerHTML = "最大瞬間風速風向";
          row_1.appendChild(heading_1);
          row_1.appendChild(heading_2);
          row_1.appendChild(heading_3);
          row_1.appendChild(heading_4);
          thead.appendChild(row_1);

          for (var i = 0; i < result.amedas_data_array.length; i++) 
          {
            let row_2 = document.createElement('tr');
            let row_2_data_1 = document.createElement('td');
            row_2_data_1.innerHTML = result.amedas_data_array[i].date;
            let row_2_data_2 = document.createElement('td');
            row_2_data_2.innerHTML = result.amedas_data_array[i].pricipitation;
            let row_2_data_3 = document.createElement('td');
            row_2_data_3.innerHTML = result.amedas_data_array[i].wind_speed;
            let row_2_data_4 = document.createElement('td');
            row_2_data_4.innerHTML = result.amedas_data_array[i].wind_direction;
            row_2.appendChild(row_2_data_1);
            row_2.appendChild(row_2_data_2);
            row_2.appendChild(row_2_data_3);
            row_2.appendChild(row_2_data_4);
            tbody.appendChild(row_2);
          }
        } 
        else 
        {
          console.log("state 4")
          document.getElementById("loading").textContent = "サーバエラー"
        }
      } 
      else 
      {
        console.log("failed.")
        document.getElementById("loading").textContent = "該当する検索結果は見つかりませんでした。"
      }
    } 
    catch (e) 
    {
      
    }
  };
}

