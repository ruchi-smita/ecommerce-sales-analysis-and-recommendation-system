import json
import mysql.connector
from datetime import datetime, timedelta

# Database connection
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="ecommerce_db"
)

cursor = conn.cursor(dictionary=True)

# 1️⃣ Total quantity sold
cursor.execute("""
    SELECT 
        oi.product_id,
        SUM(oi.quantity) AS total_sold
    FROM order_items oi
    GROUP BY oi.product_id
""")
sales_data = cursor.fetchall()

sales_map = {row["product_id"]: row["total_sold"] for row in sales_data}

# 2️⃣ Recent purchases (last 7 days)
seven_days_ago = datetime.now() - timedelta(days=7)

cursor.execute("""
    SELECT 
        product_id,
        COUNT(*) AS recent_count
    FROM user_behavior
    WHERE action = 'purchase' AND timestamp >= %s
    GROUP BY product_id
""", (seven_days_ago,))
recent_data = cursor.fetchall()

recent_map = {row["product_id"]: row["recent_count"] for row in recent_data}

# 3️⃣ Calculate trend score
trend_scores = []

for product_id in set(list(sales_map.keys()) + list(recent_map.keys())):
    total_sold = float(sales_map.get(product_id, 0))
    recent = float(recent_map.get(product_id, 0))

    score = (total_sold * 0.6) + (recent * 0.4)

    trend_scores.append({
        "product_id": product_id,
        "score": round(score, 2)
    })

# 4️⃣ Sort & take top 5
trend_scores = sorted(trend_scores, key=lambda x: x["score"], reverse=True)[:5]

print(json.dumps(trend_scores))

cursor.close()
conn.close()
