<?php

declare(strict_types=1);

namespace Williarin\Cook\Merger;

final class TextMerger extends AbstractMerger
{
    public static function getName(): string
    {
        return 'text';
    }

    public function merge(array $file): void
    {
        if (($input = $this->getSourceContent($file)) === null) {
            return;
        }

        $input = $this->wrapRecipeId(rtrim($input, "\n"));
        $destinationPathname = $this->getDestinationRealPathname($file);
        $output = file_exists($destinationPathname) ? file_get_contents($destinationPathname) : '';
        $updated = false;

        if (
            preg_match(sprintf(
                '/(%s.*%s)/smU',
                preg_quote($this->getRecipeIdOpeningComment(), '/'),
                preg_quote($this->getRecipeIdClosingComment(), '/'),
            ), $output, $match)
        ) {
            if ($this->state->getOverwrite() && $match[1] !== trim($input)) {
                $output = str_replace($match[1], trim($input), $output);
                $updated = true;
            }
        } else {
            if ($output !== '') {
                $output .= "\n";
            }

            $output .= $input;
            $updated = true;
        }

        if (!$updated) {
            return;
        }

        $fileExists = file_exists($destinationPathname);
        $this->filesystem->mkdir(\dirname($destinationPathname), 0755);
        file_put_contents($destinationPathname, $output);

        $this->io->write(sprintf('%s file: %s', $fileExists ? 'Updated' : 'Created', $destinationPathname));
    }
}