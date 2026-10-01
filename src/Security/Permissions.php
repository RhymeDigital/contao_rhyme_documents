<?php

/**
 * Copyright (C) 2026 Rhyme Digital, LLC.
 *
 * @link       https://rhyme.digital
 * @license    http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

declare(strict_types=1);

namespace Rhyme\ContaoDocumentsBundle\Security;

class Permissions
{
    public const USER_CAN_EDIT_DOCUMENTS = 'contao_user.document';
    public const USER_CAN_CREATE_DOCUMENTS = 'contao_user.document.create';
    public const USER_CAN_DELETE_DOCUMENTS = 'contao_user.document.delete';
}