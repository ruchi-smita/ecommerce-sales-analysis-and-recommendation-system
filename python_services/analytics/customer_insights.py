import json
from python_services.db_connect import get_connection

conn = get_connection()
cursor = conn.cursor(dictionary=True)

query = """
SELECT 
    u.user_id,
    u.name,
    u.email,
    COUNT(o.order_id) AS total_orders,
    SUM(o.total_amount) AS total_spent,
    MAX(o.order_date) AS last_order_date
FROM users u
JOIN orders o ON u.user_id = o.user_id
GROUP BY u.user_id, u.name, u.email
ORDER BY total_spent DESC
LIMIT 10
"""

cursor.execute(query)
results = cursor.fetchall()

cursor.close()
conn.close()

clean_results = [
    {
        "user_id": row["user_id"],
        "name": row["name"],
        "email": row["email"],
        "total_orders": int(row["total_orders"]),
        "total_spent": float(row["total_spent"]),
        "last_order_date": row["last_order_date"].isoformat()
    }
    for row in results
]

print(json.dumps(clean_results))
