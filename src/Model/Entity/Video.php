<?php

/*
 * This software is licensed under the Apache 2 license, quoted below.
 *
 * Copyright 2015 NV3
 * Copyright 2015 Vladimir Stračkovski <vlado@nv3.org>

 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace nv\Simplex\Model\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Video class
 *
 * A media item of type video.
 *
 * @Entity
 * @Table(name="videos")
 * @HasLifecycleCallbacks
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Video extends MediaItem
{
    /**
     * Video file
     *
     * @var UploadedFile
     *
     * @Assert\File(
     *     maxSize = "300M",
     *     mimeTypes = {"video/mp4", "video/mov", "video/avi"},
     *     maxSizeMessage = "The maximum allowed file size is 300MB.",
     *     mimeTypesMessage = "Only the file types image are allowed."
     * )
     */
    protected $file;

    /**
     * @return string
     */
    public function getType()
    {
        return 'video';
    }
}
