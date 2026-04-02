import random
cities=['Paris','Lyon','Marseille','Bordeaux','Toulouse','Nice','Nantes','Strasbourg','Lille','Rennes','Montpellier','Grenoble','Tours','Dijon','Reims']
map_driver={3:[1,3],4:[2],6:[4,7],8:[5,8],9:[6]}
eco={1:1,2:1,3:0,4:1,5:1,6:1,7:0,8:0}
rows=[]
years=[2025,2026,2027]
for y in years:
    for i in range(70):
        driver=random.choice(list(map_driver.keys()))
        vehicle=random.choice(map_driver[driver])
        from_city,to_city=random.sample(cities,2)
        month=random.randint(1,12)
        day=random.randint(1,28)
        hour=random.randint(6,20)
        minute=random.choice([0,15,30,45])
        dt=f"{y:04d}-{month:02d}-{day:02d} {hour:02d}:{minute:02d}:00"
        price=round(random.uniform(10.0,45.0),2)
        total_seats=random.randint(2,5)
        rows.append((driver,vehicle,from_city,to_city,dt,price,total_seats,total_seats,eco[vehicle]))
random.shuffle(rows)
for r in rows[:210]:
    print(f"INSERT INTO carpools (driver_id, vehicle_id, from_city, to_city, departure_datetime, price, total_seats, seats_available, is_eco) VALUES ({r[0]}, {r[1]}, '{r[2]}', '{r[3]}', '{r[4]}', {r[5]:.2f}, {r[6]}, {r[7]}, {r[8]});")
