<?php $__env->startSection('title', 'Verify Your Email'); ?>

<?php $__env->startSection('content'); ?>
    <h2>Verify Your Email Address</h2>
    
    <p>Hello <?php echo e($user->name); ?>,</p>
    
    <p>Thank you for registering with CAS Private Care! Please verify your email address by clicking the button below:</p>
    
    <div style="text-align: center;">
        <a href="<?php echo e($verificationUrl); ?>" class="button">Verify Email Address</a>
    </div>
    
    <p>Or copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #2563eb;"><?php echo e($verificationUrl); ?></p>
    
    <p>This link will expire in 24 hours.</p>
    
    <p>If you did not create an account with CAS Private Care, please ignore this email.</p>
    
    <p>Best regards,<br>The CAS Private Care Team</p>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('emails.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\resources\views/emails/verification.blade.php ENDPATH**/ ?>