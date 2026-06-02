import pymysql
conn = pymysql.connect(host='127.0.0.1', user='root', password='root', database='local', port=10017, charset='utf8', cursorclass=pymysql.cursors.DictCursor)
with conn.cursor() as cur:
  cur.execute("SELECT option_name, option_value FROM wp_options WHERE option_name IN ('template','stylesheet','active_plugins')")
  rows = cur.fetchall()
  print(rows)
conn.close()
