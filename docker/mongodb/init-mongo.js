db = db.getSiblingDB('orders_read');

db.createUser({
  user: 'mongo',
  pwd: 'mongo',
  roles: [
    {
      role: 'readWrite',
      db: 'orders_read'
    }
  ]
});

print('User "mongo" created for database "orders_read"');
