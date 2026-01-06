

<?php $__env->startSection('title', 'Booking Approved'); ?>

<?php $__env->startSection('content'); ?>
    <h2>Booking Approved!</h2>
    
    <p>Hello <?php echo e($clientName); ?>,</p>
    
    <p>Great news! Your booking has been approved.</p>
    
    <div style="background-color: #f9fafb; padding: 20px; border-radius: 6px; margin: 20px 0;">
        <h3 style="margin-top: 0;">Booking Details:</h3>
        <p><strong>Service Type:</strong> <?php echo e($booking->service_type); ?></p>
        <p><strong>Service Date:</strong> <?php echo e(\Carbon\Carbon::parse($booking->service_date)->format('F d, Y')); ?></p>
        <?php if($booking->duty_type): ?>
            <p><strong>Duty Type:</strong> <?php echo e($booking->duty_type); ?></p>
        <?php endif; ?>
        <?php if($booking->duration_days): ?>
            <p><strong>Duration:</strong> <?php echo e($booking->duration_days); ?> day(s)</p>
        <?php endif; ?>
        <?php if($booking->hourly_rate): ?>
            <p><strong>Rate:</strong> $<?php echo e(number_format($booking->hourly_rate, 2)); ?>/hour</p>
        <?php endif; ?>
        <?php if($booking->city || $booking->county): ?>
            <p><strong>Location:</strong> <?php echo e($booking->city ?? ''); ?><?php echo e($booking->city && $booking->county ? ', ' : ''); ?><?php echo e($booking->county ?? ''); ?></p>
        <?php endif; ?>
    </div>
    
    <p>Your booking is now active. We will assign caregivers to your booking soon, and you will be notified once they are assigned.</p>
    
    <p>You can view your booking details and status by logging into your dashboard.</p>
    
    <p>If you have any questions or need to make changes, please contact our support team.</p>
    
    <p>Best regards,<br>The CAS Private Care Team</p>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\resources\views/emails/booking-approved.blade.php ENDPATH**/ ?>