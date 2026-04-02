const fs = require('fs');
const cities=['Paris','Lyon','Marseille','Bordeaux','Toulouse','Nice','Nantes','Strasbourg','Lille','Rennes','Montpellier','Grenoble','Tours','Dijon','Reims'];
const map_driver={3:[1,3],4:[2],6:[4,7],8:[5,8],9:[6]};
const eco={1:1,2:1,3:0,4:1,5:1,6:1,7:0,8:0};
const rows=[];
const years=[2025,2026,2027];
for (const y of years){
  for (let i=0;i<70;i++){
    const drivers=Object.keys(map_driver).map(n=>Number(n));
    const driver=drivers[Math.floor(Math.random()*drivers.length)];
    const vehicles=map_driver[driver];
    const vehicle=vehicles[Math.floor(Math.random()*vehicles.length)];
    const [from_city, to_city]=cities.sort(()=>Math.random()-0.5).slice(0,2);
    const month=1+Math.floor(Math.random()*12);
    const day=1+Math.floor(Math.random()*28);
    const hour=6+Math.floor(Math.random()*15);
    const minute=[0,15,30,45][Math.floor(Math.random()*4)];
    const departure_datetime=`${y.toString().padStart(4,'0')}-${month.toString().padStart(2,'0')}-${day.toString().padStart(2,'0')} ${hour.toString().padStart(2,'0')}:${minute.toString().padStart(2,'0')}:00`;
    const price=(10 + Math.random()*35).toFixed(2);
    const total_seats=2+Math.floor(Math.random()*4);
    const is_eco=eco[vehicle];
    rows.push({driver,vehicle,from_city,to_city,departure_datetime,price,total_seats,seats_available:total_seats,is_eco});
  }
}
rows.sort(()=>Math.random()-0.5);
const lines=rows.slice(0,210).map(r=>`INSERT INTO carpools (driver_id, vehicle_id, from_city, to_city, departure_datetime, price, total_seats, seats_available, is_eco) VALUES (${r.driver}, ${r.vehicle}, '${r.from_city}', '${r.to_city}', '${r.departure_datetime}', ${r.price}, ${r.total_seats}, ${r.seats_available}, ${r.is_eco});`);
fs.writeFileSync('carpools_inserts.sql', lines.join('\n'));
console.log('Done', lines.length);
