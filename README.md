# WP Sgv
Sgvizlerを使用してSPARQLクエリの結果をビジュアライズするためのWordPressプラグインです。

### 利用方法
以下のように囲み型ショートコードを記述します。
SPARQLクエリは囲みの中に記述します。
```
[wp_sgv endpoint="http://ja.dbpedia.org/sparql" chart="google.visualization.BarChart" options="chartArea:{left:'100'},title:'The number of cities per prefecture',titleTextStyle:{color:'red', fontSize:'20'},legend:{position:'none'},hAxis:{gridlines:{color:'red'}}" height="600" width="600"]
  SELECT DISTINCT ?kenmei, COUNT(?shi) AS ?cities WHERE {
    ?ken rdf:type schema:AdministrativeArea ;
    dbpedia-owl:country dbpedia-ja:日本 ;
    prop-ja:name ?kenmei .
    ?shi rdf:type dbpedia-owl:City ;
    dbpedia-owl:location ?ken . }
  GROUP BY ?kenmei
  ORDER BY DESC(?cities)
  LIMIT 10
[/wp_sgv]
```
ショートコードの属性は以下のとおりです。

|属性|説明|
|:--|:--|
|endpoint|SPARQLエンドポイントを指定します。|
|chart|チャートの種類を指定します。|
|options|チャートのオプションを指定します。有効なJavaScriptコードとして記述する必要があります。|
|height|チャート描画領域の高さを指定します。|
|width|チャート描画領域の幅を指定します。|

プラグインをインストールすると、投稿や固定ページの編集画面に以下のようなメタボックスが追加され、メタボックス内でショートコードを生成することができます。

![メタボックス](http://midoriit.com/images/2014/03/wpsgv3.png)

生成されたショートコードを編集画面にコピペして使用します。

### インストール方法
ZIPファイルをダウンロードし、解凍してできたwp-sgvフォルダを、WordPressのインストール先のwp-content/pluginsの中にコピーします。WordPressのプラグイン管理ページにWP Sgvプラグインが登録されますので、「有効化」します。

