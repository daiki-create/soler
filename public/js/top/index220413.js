
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
            getPrefectureSelection('#area_select_prefecture', '#area_select_city', 'https://weather-info-ss.com/climate/public/json/prefectureCity.json', valType, initPrefVal, initCityVal);
    
            // 都道府県選択時に市区町村リストを作成
            $('#area_select_prefecture').on('change', function() {
              getCitySelection('#area_select_prefecture', '#area_select_city', 'https://weather-info-ss.com/climate/public/json/prefectureCity.json', valType);
            });
          });
        }
    }
  }
})


// 検索（非同期通信）
function getData() {

  // メッセージ初期化
  document.getElementById("loading").textContent = "検索中です..."
  document.getElementById("loading").style.margin = "1rem 0 0 0"
  document.getElementById("result-msg-1").textContent = ""

  // テーブル初期化
  if(document.getElementById("soler-table").childElementCount > 0)
  {
    document.getElementById("soler-table").innerHTML = ""
  }
  
  if(document.getElementById("area_select_prefecture"))
  {
    var area = document.getElementById("area_select_prefecture").value + document.getElementById("area_select_city").value
  }
  else if(document.getElementById("area_manual"))
  {
    var area = document.getElementById("area_manual").value
  }
  var unique = document.getElementById("unique_select").value
  var output = document.getElementById("output_select").value
  var adress_blank = document.getElementById("adress_blank_select").value
  var data = {
    "area": area,
    "unique": unique,
    "output": output,
    "adress_blank": adress_blank,
  }
  console.log(data)
  
  if(data.area == "")
  {
    document.getElementById("loading").textContent = "地点を入力してください"
    console.log(false_data)
  }

  var json = JSON.stringify(data)
  var xhr = new XMLHttpRequest()
  console.log(location.href+"/api")
  xhr.open("POST", location.href+"/api")
  xhr.setRequestHeader("content-type", "application/x-www-form-urlencoded;charset=UTF-8")
  xhr.send(json);

  xhr.onreadystatechange = function () {
    try 
    {
      if (xhr.readyState == 4) 
      {
        console.log("state 4.")
        if (xhr.status == 200) 
        {
          console.log("200 ok.")

          console.log(xhr.response);
          var result = JSON.parse(xhr.response);
          console.log("result:")
          console.log(result);

          document.getElementById("loading").textContent = ""
          document.getElementById("loading").style.margin = 0
            
          // 太陽光パネルデータ表作成
          if(result.soler_data_array.length > 0)
          {
            document.getElementById("result-msg-1").textContent = "検索結果："+result.soler_data_array.length+"件"
            page = 0;
            lastPage = Math.floor(result.soler_data_array.length / 100 + 1);
            for (var i = 0; i < result.soler_data_array.length; i++) 
            {
              // 100の倍数で改ページ
              if(i % 100 == 0)
              {
                page++;
                var tableParent = document.createElement('div');
                tableParent.style.backgroundColor = "white";
                tableParent.id = "table-parent"+String(page)

                // ページネーション
                var pageNation = document.createElement('div');
                pageNation.classList = "pagenation";
                var pageNext = document.createElement('button');
                var pageBack = document.createElement('button');
                pageNext.textContent = "次へ";
                pageBack.textContent = "前へ";
                pageNext.setAttribute('onclick', 'pageNext('+String(page)+')');
                pageBack.setAttribute('onclick', 'pageBack('+String(page)+')');
                if(page != 1){
                  pageNation.appendChild(pageBack);
                }
                if(page != lastPage){
                  pageNation.appendChild(pageNext);
                }
                tableParent.appendChild(pageNation);

                // 2ページ目以降非表示
                if(page != 1)
                {
                  tableParent.style.display = 'none';
                }
                document.getElementById("soler-table").appendChild(tableParent);
  
                var table = document.createElement('table');
                var table_inner = document.createElement('div');
                var thead = document.createElement('thead');
                var tbody = document.createElement('tbody');
                table_inner.appendChild(table);
                table.appendChild(thead);
                table.appendChild(tbody);
                
                tableParent.appendChild(table);
                tableParent.style.overflowX = 'scroll';
                var row_1 = document.createElement('tr');
                row_1.classList = ["bgc-gray"];

                var heading_1 = document.createElement('th');
                heading_1.innerHTML = "設備ID";
                var heading_2 = document.createElement('th');
                heading_2.innerHTML = "発電所事業者名";
                var heading_3 = document.createElement('th');
                heading_3.innerHTML = "代表者名";
                var heading_4 = document.createElement('th');
                heading_4.innerHTML = "事業者の住所";
                var heading_5 = document.createElement('th');
                heading_5.innerHTML = "事業者の電話番号";
                // var heading_6 = document.createElement('th');
                // heading_6.innerHTML = "発電設備区分";
                var heading_7 = document.createElement('th');
                heading_7.innerHTML = "発電出力(kW)";
                var heading_8 = document.createElement('th');
                heading_8.innerHTML = "発電所の所在地";
                var heading_9 = document.createElement('th');
                heading_9.innerHTML = "太陽光電池の合計出力(kW)";
             
                row_1.appendChild(heading_1);
                row_1.appendChild(heading_2);
                row_1.appendChild(heading_3);
                row_1.appendChild(heading_4);
                row_1.appendChild(heading_5);
                // row_1.appendChild(heading_6);
                row_1.appendChild(heading_7);
                row_1.appendChild(heading_8);
                row_1.appendChild(heading_9);
             
                thead.appendChild(row_1);
              }
              var row_2 = document.createElement('tr');

              var row_2_data_1 = document.createElement('td');
              row_2.appendChild(row_2_data_1);
              row_2_data_1.innerHTML = result.soler_data_array[i].facility_id;

              var row_2_data_2 = document.createElement('td');
              row_2.appendChild(row_2_data_2);
              row_2_data_2.innerHTML = result.soler_data_array[i].name;

              var row_2_data_3 = document.createElement('td');
              row_2.appendChild(row_2_data_3);
              row_2_data_3.innerHTML = result.soler_data_array[i].representative_name;

              var row_2_data_4 = document.createElement('td');
              row_2.appendChild(row_2_data_4);
              row_2_data_4.innerHTML = result.soler_data_array[i].adress;

              var row_2_data_5 = document.createElement('td');
              row_2.appendChild(row_2_data_5);
              row_2_data_5.innerHTML = result.soler_data_array[i].tel;

              // var row_2_data_6 = document.createElement('td');
              // row_2.appendChild(row_2_data_6);
              // row_2_data_6.innerHTML = result.soler_data_array[i].type;

              var row_2_data_7 = document.createElement('td');
              row_2.appendChild(row_2_data_7);
              row_2_data_7.innerHTML = result.soler_data_array[i].output;

              var row_2_data_8 = document.createElement('td');
              row_2.appendChild(row_2_data_8);
              row_2_data_8.innerHTML = result.soler_data_array[i].facility_adress;

              var row_2_data_9 = document.createElement('td');
              row_2.appendChild(row_2_data_9);
              row_2_data_9.innerHTML = result.soler_data_array[i].total_output;
             
              tbody.appendChild(row_2);
            }
          }
          else{
            document.getElementById("loading").textContent = "該当データはありません"
          }
          console.log("try success.");
        } 
        else 
        {
          console.log("state 4 but error.")
          document.getElementById("loading").textContent = "レスポンスエラー"
        }
      } 
      else 
      {
        console.log("failed.")
      }
    } 
    catch (e) 
    {
      console.log("catch.")
      document.getElementById("loading").textContent = "エラー"
    }
  };
}

function pageNext(page)
{
  nextPage = page+1;
  document.getElementById("table-parent"+page).style.display = "none";
  document.getElementById("table-parent"+nextPage).style.display = "block";
}

function pageBack(page)
{
  backPage = page-1;
  document.getElementById("table-parent"+page).style.display = "none";
  document.getElementById("table-parent"+backPage).style.display = "block";
}

// function visibleAggregated()
// {
//   document.getElementById("aggregated_table").style.display = "block";
//   document.getElementById("search").style.display = "none";
//   document.getElementById("btn2").style.background = "linear-gradient(#888, #aaa)";
//   document.getElementById("btn1").style.background = "linear-gradient(#666, #888)";
// }

// function visibleSearch()
// {
//   document.getElementById("aggregated_table").style.display = "none";
//   document.getElementById("search").style.display = "block";
//   document.getElementById("btn2").style.background = "linear-gradient(#666, #888)";
//   document.getElementById("btn1").style.background = "linear-gradient(#888, #aaa)";
// }