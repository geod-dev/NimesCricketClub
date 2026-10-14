<?php

namespace App\Tests;

use App\Entity\ContactSubmission;
use App\Service\ContactFormService;
use App\Service\LLMService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ContactFormDeliveryTest extends TestCase
{
    public function deliveryOutcomes(): array
    {
        return [[true], [false]];
    }

    /** @dataProvider deliveryOutcomes */
    public function testContactAttachmentsAndFailureCleanup(bool $delivered): void
    {
        $filesystem = new Filesystem();
        $directory = sys_get_temp_dir() . '/club-contact-' . bin2hex(random_bytes(8));
        $filesystem->mkdir($directory);
        $filesystem->dumpFile($directory . '/upload.txt', 'Club attachment');

        try {
            $file = new UploadedFile($directory . '/upload.txt', 'rules.txt', 'text/plain', null, true);
            $field = $this->createMock(FormInterface::class);
            $field->method('getData')->willReturn([$file]);
            $form = $this->createMock(FormInterface::class);
            $form->method('get')->with('attachments')->willReturn($field);
            $llm = $this->createMock(LLMService::class);
            $llm->method('query')->willReturn('true');
            $mail = $this->createMock(MailService::class);
            $mail->expects(self::once())->method('sendMessage')->with(
                'contact@nimescricketclub.fr', 'Member',
                'Message de Member via le formulaire de contact',
                self::callback(fn ($html) => str_contains($html, '&lt;script&gt;')),
                self::callback(fn ($attachments) => $attachments[0]['content'] === 'Club attachment'
                    && $attachments[0]['name'] === 'rules.txt')
            )->willReturnCallback(function () use ($delivered) {
                if (!$delivered) {
                    throw new RuntimeException('Rejected');
                }
            });
            $manager = $this->createMock(EntityManagerInterface::class);
            $manager->expects($delivered ? self::once() : self::never())->method('flush');
            $submission = (new ContactSubmission())
                ->setName('Member')->setEmail('member@example.org')->setContent('<script>Message</script>');
            $service = new ContactFormService($directory, $llm, $filesystem, $manager, $mail);

            try {
                $service->handleFormSubmission($form, $submission);
                self::assertTrue($delivered);
            } catch (RuntimeException $e) {
                self::assertFalse($delivered);
                self::assertSame('Rejected', $e->getMessage());
            }

            self::assertCount(1, $submission->getAttachments());
            $attachment = $submission->getAttachments()->first();
            self::assertTrue($attachment->isPrivate());
            self::assertSame($delivered, file_exists($directory . '/' . $attachment->getPath()));
        } finally {
            $filesystem->remove($directory);
        }
    }
}
