create table if not exists CompanyEvent
(
    eventDateTimeOnClient DateTime64(3),
    clientTimezone String,
    eventReceivingDateTimeOnServer DateTime64(6),
    clientIp IPv4,
    userAgent String,
    sourceHostName String,
    sourcePathName String,
    nodeEnName String,
    companyId UInt32,
    tgbClick UInt8,
    tgbView UInt8,
    tgbIsBanner UInt8,
    tgbPosition String,
    tgbId UInt32,
    tgbTargetUrl String,
    showPhone UInt8,
    showSite UInt8,
    showCompanyPage UInt8,
    visitSiteFromCompanyPage UInt8,
    showCompanyCardInList UInt8,
    clickCompanyCardInList UInt8,
    listType Nullable(Enum8('rubric' = 1, 'search' = 2, 'map' = 3)) default NULL,
    visitSiteFromCompanyCard UInt8,
    rubricId UInt64
)
engine = MergeTree PARTITION BY toYYYYMM(eventDateTimeOnClient)
ORDER BY (eventDateTimeOnClient, companyId)
SETTINGS index_granularity = 8192;
