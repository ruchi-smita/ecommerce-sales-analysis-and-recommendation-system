import json
from python_services.db_connect import get_connection
from decimal import Decimal
from datetime import date


conn = get_connection()
cursor = conn.cursor(dictionary=True)

query = """
SELECT DATE(order_date) AS date,
       SUM(total_amount) AS revenue
FROM orders
GROUP BY DATE(order_date)
ORDER BY date DESC
"""

cursor.execute(query)
results = cursor.fetchall()

cursor.close()
conn.close()


clean_results = []

for row in results:
    clean_results.append({
        "date": row["date"].isoformat(),      # date → string
        "revenue": float(row["revenue"])      # Decimal → float
    })

print(json.dumps(clean_results))
