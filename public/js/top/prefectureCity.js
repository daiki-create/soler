function getPrefectureSelection(prefSelectionId, citySelectionId, jsonPath, valType='code', initPrefVal=null, initCityVal=null) {
    // JSONファイルから都道府県を取得
    $.getJSON(jsonPath, function (data) {
      $.grep(data,
        function (obj, idx) {
          // Get the prefecture.
          if (!obj.cityName) {
            let selected = '';
            if (initPrefVal && (
              (valType === 'code' && obj.groupCode.startsWith(('00' + initPrefVal).slice(-2)))
              || (valType === 'name' && obj.prefectureName === initPrefVal))
            ) {
              selected = ' selected';
              // 都道府県選択時に市区町村リストを作成
              getCitySelection(prefSelectionId, citySelectionId, jsonPath, valType, initPrefVal, initCityVal, obj.groupCode.slice(0, 2));
            }
            if (valType === 'code') {
              $(prefSelectionId).append('<option value="' + obj.groupCode.slice(0, 2) + '" data-pref-id="' + obj.groupCode.slice(0, 2) + '"' + selected + '>' + obj.prefectureName + '</option>');
            } else {
              $(prefSelectionId).append('<option value="' + obj.prefectureName + '" data-pref-id="' + obj.groupCode.slice(0, 2) + '"' + selected + '>' + obj.prefectureName + '</option>');
            }
          }
        }
      );
    });
  }
  
  function getCitySelection(prefSelectionId, citySelectionId, jsonPath, valType='code', initPrefVal=null, initCityVal=null, selectedPrefCode=null) {
    // 市区町村リストのリセット
    $(citySelectionId + ' option:nth-child(n+2)').remove();
  
    // 都道府県コードの取得
    let prefCode = '01';
    if (selectedPrefCode) {
      prefCode = selectedPrefCode;
    } else if (initPrefVal) {
      prefCode = ('00' + $(prefSelectionId + ' option[value="' + initPrefVal + '"]').data('pref-id')).slice(-2);
      $(prefSelectionId + ' option[value="' + initPrefVal + '"]').prop('selected', true);
    } else {
      prefCode = ('00' + $(prefSelectionId + ' option:selected').data('pref-id')).slice(-2);
    }
  
    // JSONファイルから市区町村を取得
    $.getJSON(jsonPath, function (data) {
      $.grep(data,
        function (obj, idx) {
          // 都道府県コード参加の市区町村コードを取得
          if (obj.groupCode.startsWith(prefCode) && obj.cityName) {
            let selected = '';
            if (initCityVal && (
              (valType === 'code' && obj.groupCode.startsWith(prefCode + ('00' + initCityVal).slice(-3)))
              || (valType === 'name' && obj.cityName === initCityVal))
            ) {
              // 市区町村の初期値を設定
              selected = ' selected';
            }
            if (valType === 'code') {
              $(citySelectionId).append('<option value="' + obj.groupCode.slice(2, 5) + '"' + selected + '>' + obj.cityName + '</option>');
            } else {
              $(citySelectionId).append('<option value="' + obj.cityName + '"' + selected + '>' + obj.cityName + '</option>');
            }
          }
        }
      );
    });
  }