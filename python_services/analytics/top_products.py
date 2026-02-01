import json
from python_services.db_connect import get_connection

conn = get_connection()
cursor = conn.cursor(dictionary=True)

query = """
SELECT 
    p.product_id,
    p.name,
    SUM(oi.quantity) AS total_sold,
    SUM(oi.quantity * oi.price) AS revenue
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
GROUP BY oi.product_id, p.name
ORDER BY total_sold DESC
LIMIT 10
"""

cursor.execute(query)
results = cursor.fetchall()

cursor.close()
conn.close()

clean_results = [
    {
        "product_id": row["product_id"],
        "name": row["name"],
        "total_sold": int(row["total_sold"]),
        "revenue": float(row["revenue"])
    }
    for row in results
]

print(json.dumps(clean_results))
