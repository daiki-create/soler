<!DOCTYPE html>
<html lang="ja">
<head>
    <title>太陽光パネル所有者検索</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="Keywords" content="" />
    <meta name="Description" content="" />
    <!-- <meta name="viewport" content="width=device-width,initial-scale=1"> -->

    <link rel="icon" href="<?php echo $this->config->item('base_url') ?>/img/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo $this->config->item('base_url') ?>/img/favicon.ico" type="img/x-icon" />
    <link href="<?php echo $this->config->item('base_url') ?>/css/top/index220413.css" rel="stylesheet">

    <!-- Vue.js -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
    
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
    <header>
        <h2>太陽光パネル所有者検索</h2>
    </header>

    <div id="main">
        <!-- <div id="toggle-btn">
            <button id="btn1" onclick="visibleAggregated()">太陽光パネルの数</button>
            <button id="btn2" onclick="visibleSearch()">検索</button>
        </div>
        <div id="card"> -->
            <!-- <div id="aggregated_table" class="aggregated_table">
                <p>
                    <span>※同一住所の表記による差異は「重複でない」とみなします。</span>
                </p>
                <table>
                    <tbody>
                        <tr class="bgc-gray">
                            <th>発電設備の<br>所在地</th>
                            <th>すべて</th>
                            <th><span style="color:blue">所有者住所の重複なし</span></th>
                            <th><span style="color:red">発電出力50kW以下</th>
                            <th><span style="color:red">発電出力50kW以下</span><br><span style="color:blue">所有者住所の重複無し</span></th>
                            <th><span style="color:red">発電出力50kW以下</span><br><span style="color:blue">所有者住所の重複無し</span><br><span style="color:green">住所記載あり</span></th>
                        </tr>
                        <?php 
                        // foreach($soler_aggregated_array as $row){
                        //     echo("<tr></tr>");
                        //     echo("<td>".$row['prefecture']."</td>");
                        //     echo("<td>".$row['n_soler']."</td>");
                        //     echo("<td>".$row['n_soler_unique_address']."</td>");
                        //     echo("<td>".$row['n_soler_under_50']."</td>");
                        //     echo("<td>".$row['n_soler_under_50_unique_address']."</td>");
                        //     echo("<td>".$row['n_soler_under_50_unique_address_not_blank']."</td>");
                        //     echo("<tr></tr>");
                        // }
                        ?>
                    </tbody>
                </table>
            </div> -->

            <div id="search">
            <div id="form">
                <form method="" action="" onsubmit="return false" >

                    <!-- 地点 -->
                    <div id="area">
                        <select v-on:change="toggleForm">
                            <option value="area-input-select">発電設備の所在地を選択</option>
                            <option value="area-input-manual">発電設備の所在地を手動で入力</option>
                        </select>

                        <div v-if="isActiveSelect">
                            <select id="area_select_prefecture" class="" required>
                                <option value="">都道府県を選択</option>
                            </select>
                            <select id="area_select_city" class="">
                                <option value="">市区町村を選択</option>
                            </select>
                        </div>

                        <div v-if="isActiveManual">
                            <input id="area_manual" type="text" required>
                        </div>
                    </div>

                    <div>
                        <!-- 重複有無 -->
                        <div id="unique">
                            <label class="font-weight-bold" for="">重複</label>
                            <select id="unique_select" name="">
                                <option value=0>
                                    あり
                                </option>
                                <option value=1>
                                    なし
                                </option>
                            </select>
                        </div>

                        <!-- 発電出力 -->
                        <div id="output">
                            <label class="font-weight-bold" for="">発電出力</label>
                            <select id="output_select" name="">
                                <option value=0>
                                    制限なし
                                </option>
                                <option value=1>
                                    50kW以下
                                </option>
                            </select>
                        </div>

                        <!-- 住所記載有無 -->
                        <div id="adress_blank">
                            <label class="font-weight-bold" for="">事業者の住所記載</label>
                            <select id="adress_blank_select" name="">
                                <option value=0>
                                    指定なし
                                </option>
                                <option value="no_blank">
                                    あり
                                </option>
                                <option value="blank">
                                    未記入
                                </option>
                            </select>
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

                    <div id="search_button">
                        <button onclick="getData()">検索</button>
                    </div>

                    <p id="loading"></p>
                </form>
            </div>
            
            <div id="result-msg" class="font-weight-bold">
                <p id="result-msg-1"></p>
            </div>

            <div id="rotate-wrapper">
                <div id="soler-table"></div>
            </div>
            </div>
           
        </div>
    <!-- </div> -->
    <script src="<?php echo $this->config->item('base_url') ?>/js/top/index220413.js"></script>
</body>
</html>