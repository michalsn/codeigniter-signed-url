<?php

namespace Michalsn\CodeIgniterSignedUrl\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Michalsn\CodeIgniterSignedUrl\Exceptions\SignedUrlException;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

/**
 * SignedUrl filter.
 *
 * This filter is not intended to be used from the command line.
 */
class SignedUrl implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param array|null $arguments
     *
     * @return RedirectResponse|void
     *
     * @throws PageNotFoundException|SignedUrlException
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        $signedUrl = service('signedurl');

        try {
            $signedUrl->verify($request);
        } catch (SignedUrlException $e) {
            if ($signedUrl->shouldRedirect()) {
                return redirect()->back()->with('error', $e->getMessage());
            }

            if ($signedUrl->shouldShow404()) {
                throw PageNotFoundException::forPageNotFound($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * We don't have anything to do here.
     *
     * @param array|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
