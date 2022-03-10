<!DOCTYPE html>
<html lang="ja">
<head>
    <title>過去のアメダス・ライデンデータ検索</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="Keywords" content="気象庁,過去の雷観測,雷観測" />
    <meta name="Description" content="過去のアメダス・ライデンデータを検索できます。" />
    <link rel="icon" href="<?php echo $this->config->item('base_url') ?>/img/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo $this->config->item('base_url') ?>/img/favicon.ico" type="img/x-icon" />
    <link href="<?php echo $this->config->item('base_url') ?>/css/top/index.css" rel="stylesheet">

    <!-- Vue.js -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>

    <!--leaflet-->
    <!-- <link rel = "stylesheet" href = "/css/leaflet.css" />
    <script src = "/js/leaflet.js"></script> -->
    

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id="></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-126384684-4');
    </script>
</head>

<body>
    <form method="" action="" onsubmit="return false" >

        <!-- 地点 -->
        <div id="area">
            <select v-on:change="toggleForm">
                <option value="area-input-select">地点を選択する</option>
                <option value="area-input-manual">地点を手動で入力する</option>
            </select>

            <div v-if="isActiveSelect">
                <select id="area_select_prefecture" class="">
                    <option value="">都道府県を選択</option>
                  </select>
                <select id="area_select_city" class="">
                    <option value="">市区町村を選択</option>
                </select>
            </div>

            <div v-if="isActiveManual">
                <input id="area_manual" type="text">
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="<?php echo $this->config->item('base_url') ?>/js/top/prefectureCity.js"></script>
        <script>
            $(function() {
              let initPrefVal = '都道府県を選択'; //selectedを付与したい都道府県
              let initCityVal = '市区町村を選択'; //selectedを付与したい市区町村
              let valType = 'name';  // 'name' or 'code'
      
              // 都道府県リストを作成
              getPrefectureSelection('#area_select_prefecture', '#area_select_city', '<?php echo $this->config->item('base_url') ?>/json/prefectureCity.json', valType, initPrefVal, initCityVal);
      
              // 都道府県選択時に市区町村リストを作成
              $('#area_select_prefecture').on('change', function() {
                getCitySelection('#area_select_prefecture', '#area_select_city', '<?php echo $this->config->item('base_url') ?>/json/prefectureCity.json', valType);
              });
            });
      
        </script>
     
        <!-- 期間 -->
        <div id="term">
            <label for="">期間</label>
            <div>
                <input id="start_date" type="date">
            </div>
            <div>
                <input id="end_date" type="date">
            </div>
        </div>

        <!-- 落雷有無 -->
        <div id="thander">
            <label for="">落雷有無</label>
            <select id="thander_select" name="">
                <option value="指定なし">
                    指定なし
                </option>
                <option value="あり">
                    あり
                </option>
                <option value="なし">
                    なし
                </option>
            </select>
        </div>

        <!-- 降水量 -->
        <div id="precipitation">
            <label for="">降水量(mm)</label>
            <select id="precipitation_select" name="">
                <option v-for="option in options" v-bind:value="option">
                    {{ option }}
                </option>
            </select> mm以上
        </div>

        <!-- 最大瞬間風速 -->
        <div id="wind_speed">
            <label for="">最大瞬間風速(m/s)</label>
            <select id="wind_speed_select" name="">
                <option v-for="option in options" v-bind:value="option">
                    {{ option }}
                </option>
            </select> m/s以上
        </div>

        <!-- 風向 -->
        <div id="wind_direction">
            <label for="">風向</label>
            <select id="wind_direction_select" name="">
                <option v-for="option in options" v-bind:value="option">
                    {{ option }}
                </option>
            </select>
        </div>
        
        <div id="search_button">
            <button onclick="getData()">検索</button>
        </div>

        <p id="loading"></p>
    </form>

    <p id="liden-result-msg"></p>
    <div id="map"></div>
    
    <p id="amedas-result-msg"></p>
    <div id="climate-table"></div>

    <div id="btn-create-img">
        <button>画像出力</button>
    </div>
    <div id="img"></div>

    <script src="<?php echo $this->config->item('base_url') ?>/js/top/index.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCe4LhatUzrIC8RXsPyCnDUx9s2p9L3rSQ" async defer></script>

</body>
</html>