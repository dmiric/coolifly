<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: opentelemetry/proto/collector/trace/v1/trace_service.proto

namespace Opentelemetry\Proto\Collector\Trace\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>opentelemetry.proto.collector.trace.v1.ExportTraceServiceResponse</code>
 */
class ExportTraceServiceResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The details of a partially successful export request.
     * If the request is only partially accepted
     * (i.e. when the server accepts only parts of the data and rejects the rest)
     * the server MUST initialize the `partial_success` field and MUST
     * set the `rejected_<signal>` with the number of items it rejected.
     * Servers MAY also make use of the `partial_success` field to convey
     * warnings/suggestions to senders even when the request was fully accepted.
     * In such cases, the `rejected_<signal>` MUST have a value of `0` and
     * the `error_message` MUST be non-empty.
     * A `partial_success` message with an empty value (rejected_<signal> = 0 and
     * `error_message` = "") is equivalent to it not being set/present. Senders
     * SHOULD interpret it the same way as in the full success case.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.collector.trace.v1.ExportTracePartialSuccess partial_success = 1;</code>
     */
    protected $partial_success = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Opentelemetry\Proto\Collector\Trace\V1\ExportTracePartialSuccess $partial_success
     *           The details of a partially successful export request.
     *           If the request is only partially accepted
     *           (i.e. when the server accepts only parts of the data and rejects the rest)
     *           the server MUST initialize the `partial_success` field and MUST
     *           set the `rejected_<signal>` with the number of items it rejected.
     *           Servers MAY also make use of the `partial_success` field to convey
     *           warnings/suggestions to senders even when the request was fully accepted.
     *           In such cases, the `rejected_<signal>` MUST have a value of `0` and
     *           the `error_message` MUST be non-empty.
     *           A `partial_success` message with an empty value (rejected_<signal> = 0 and
     *           `error_message` = "") is equivalent to it not being set/present. Senders
     *           SHOULD interpret it the same way as in the full success case.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Opentelemetry\Proto\Collector\Trace\V1\TraceService::initOnce();
        parent::__construct($data);
    }

    /**
     * The details of a partially successful export request.
     * If the request is only partially accepted
     * (i.e. when the server accepts only parts of the data and rejects the rest)
     * the server MUST initialize the `partial_success` field and MUST
     * set the `rejected_<signal>` with the number of items it rejected.
     * Servers MAY also make use of the `partial_success` field to convey
     * warnings/suggestions to senders even when the request was fully accepted.
     * In such cases, the `rejected_<signal>` MUST have a value of `0` and
     * the `error_message` MUST be non-empty.
     * A `partial_success` message with an empty value (rejected_<signal> = 0 and
     * `error_message` = "") is equivalent to it not being set/present. Senders
     * SHOULD interpret it the same way as in the full success case.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.collector.trace.v1.ExportTracePartialSuccess partial_success = 1;</code>
     * @return \Opentelemetry\Proto\Collector\Trace\V1\ExportTracePartialSuccess|null
     */
    public function getPartialSuccess()
    {
        return $this->partial_success;
    }

    public function hasPartialSuccess()
    {
        return isset($this->partial_success);
    }

    public function clearPartialSuccess()
    {
        unset($this->partial_success);
    }

    /**
     * The details of a partially successful export request.
     * If the request is only partially accepted
     * (i.e. when the server accepts only parts of the data and rejects the rest)
     * the server MUST initialize the `partial_success` field and MUST
     * set the `rejected_<signal>` with the number of items it rejected.
     * Servers MAY also make use of the `partial_success` field to convey
     * warnings/suggestions to senders even when the request was fully accepted.
     * In such cases, the `rejected_<signal>` MUST have a value of `0` and
     * the `error_message` MUST be non-empty.
     * A `partial_success` message with an empty value (rejected_<signal> = 0 and
     * `error_message` = "") is equivalent to it not being set/present. Senders
     * SHOULD interpret it the same way as in the full success case.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.collector.trace.v1.ExportTracePartialSuccess partial_success = 1;</code>
     * @param \Opentelemetry\Proto\Collector\Trace\V1\ExportTracePartialSuccess $var
     * @return $this
     */
    public function setPartialSuccess($var)
    {
        GPBUtil::checkMessage($var, \Opentelemetry\Proto\Collector\Trace\V1\ExportTracePartialSuccess::class);
        $this->partial_success = $var;

        return $this;
    }

}

