import json
import pymysql


conn = pymysql.connect(
    host='127.0.0.1',
    user='root',
    password='root',
    database='local',
    port=10017,
    charset='utf8mb4',
    cursorclass=pymysql.cursors.DictCursor,
)

with conn.cursor() as cur:
    cur.execute("SELECT option_name, option_value FROM wp_options WHERE option_name IN ('template','stylesheet','active_plugins')")
    print('---OPTIONS---')
    print(json.dumps(cur.fetchall(), ensure_ascii=False, indent=2))

    cur.execute(
        """
        SELECT ID, post_title, post_status, post_type
        FROM wp_posts
        WHERE post_type = 'va_listing'
          AND (post_title LIKE '%Alpex%' OR post_title LIKE '%Hikmicro%')
        ORDER BY ID DESC
        LIMIT 10
        """
    )
    listings = cur.fetchall()
    print('---LISTINGS---')
    print(json.dumps(listings, ensure_ascii=False, indent=2))

    if listings:
        listing_id = listings[0]['ID']
        cur.execute(
            """
            SELECT meta_key, meta_value
            FROM wp_postmeta
            WHERE post_id = %s
              AND meta_key LIKE 'va_%'
            ORDER BY meta_key
            """,
            (listing_id,),
        )
        print(f'---META FOR {listing_id}---')
        print(json.dumps(cur.fetchall(), ensure_ascii=False, indent=2))

conn.close()
