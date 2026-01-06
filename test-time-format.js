// Test time formatting
const startingTime = '09:00:00';
const [hours, minutes] = startingTime.split(':');
const hour = parseInt(hours);
const ampm = hour >= 12 ? 'PM' : 'AM';
const displayHour = hour % 12 || 12;
const timeFormatted = `${displayHour}:${minutes} ${ampm}`;

console.log('Start time from DB:', startingTime);
console.log('Parsed hour:', hour);
console.log('Display hour:', displayHour);
console.log('AM/PM:', ampm);
console.log('Formatted time:', timeFormatted);

// Test end time calculation
const hoursPerDay = 8;
const startHour = parseInt(hours);
const startMinutes = parseInt(minutes);
const endHour = (startHour + hoursPerDay) % 24;
const endMinutes = startMinutes;
const endPeriod = endHour >= 12 ? 'PM' : 'AM';
const endDisplayHour = endHour === 0 ? 12 : (endHour > 12 ? endHour - 12 : endHour);
const endTimeFormatted = `${endDisplayHour}:${endMinutes.toString().padStart(2, '0')} ${endPeriod}`;

console.log('\nEnd time calculation:');
console.log('Start hour:', startHour);
console.log('Add hours:', hoursPerDay);
console.log('End hour (24h):', endHour);
console.log('End display hour:', endDisplayHour);
console.log('End AM/PM:', endPeriod);
console.log('Formatted end time:', endTimeFormatted);
console.log('\nFull range:', `${timeFormatted} - ${endTimeFormatted}`);
