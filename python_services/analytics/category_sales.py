import json
from python_services.db_connect import get_connection

conn = get_connection()
cursor = conn.cursor(dictionary=True)

query = """
SELECT 
    c.category_name AS category,
    SUM(oi.quantity * oi.price) AS revenue
FROM order_items oi
JOIN products p ON oi.product_id = p.product_id
JOIN categories c ON p.category_id = c.category_id
GROUP BY c.category_name
ORDER BY revenue DESC
"""

cursor.execute(query)
results = cursor.fetchall()

cursor.close()
conn.close()

clean_results = [
    {
        "category": row["category"],
        "revenue": float(row["revenue"])
    }
    for row in results
]

print(json.dumps(clean_results))
